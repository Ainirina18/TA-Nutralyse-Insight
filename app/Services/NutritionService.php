<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NutritionService
{
    private SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    /* ACTIVE CHILD
    Ambil anak yang sedang aktif dari session.
    - Data children diubah ke object biar konsisten
    - Kalau tidak ada di session → ambil child pertama
    - Kalau ID tidak valid → fallback ke child pertama
    */
    public function getActiveChild($children)
    {
        $children = collect($children)->map(fn ($c) => (object) $c);

        $first = $children->first();

        if (!$first) return null;

        $activeChildId = session('active_child_id') ?? $children->first()->id;

        $activeChild = $children->firstWhere('id', $activeChildId);

        if (!$activeChild) {
            $activeChild = $first;
            session(['active_child_id' => $activeChild->id]);
        }

        return $activeChild;
    }

    /* TARGET AKG (Kebutuhan Harian)
    Ambil standar kebutuhan nutrisi berdasarkan umur anak
    dari tabel master_akg (PostgreSQL)
    */
    public function getAKGTargets($activeChild)
    {
        $userAge = (float) ($activeChild->age ?? 0);

        $akg = DB::connection('pgsql')
            ->table('master_akg')
            ->where('usia_min', '<=', $userAge)
            ->where('usia_max', '>=', $userAge)
            ->first();

        return [
            'energy' => (float) ($akg->energi ?? 0),
            'protein' => (float) ($akg->protein ?? 0),
            'fat' => (float) ($akg->lemak ?? 0),
        ];
    }

    /* TOTAL NUTRISI (GENERIC)
    Menjumlahkan seluruh nutrisi dari kumpulan log
    Digunakan di daily, weekly, dll
    */
    public function calculateTotals($logs)
    {
        $logs = collect($logs);

        return [
            'energy' => $logs->sum('energy'),
            'protein' => $logs->sum('protein'),
            'fat' => $logs->sum('fat'),
        ];
    }

    /* DAILY ANALYTICS
    - Hitung total nutrisi hari ini
    - Hitung sisa kebutuhan (target - konsumsi)
    */
        public function getDailyAnalytics($logs, $targetEnergy, $targetProtein, $targetFat)
        {
            $logs = collect($logs);

            $totals = $this->calculateTotals($logs);

            return [
                'totals' => $totals,
                'sisa' => [
                    'energy' => max(0, $targetEnergy - $totals['energy']),
                    'protein' => max(0, $targetProtein - $totals['protein']),
                    'fat' => max(0, $targetFat - $totals['fat']),
                ]
            ];
        }

    /* WEEKLY ANALYTICS (7 HARI TERAKHIR)
     - Total nutrisi selama 7 hari
     - Data chart per hari (untuk line chart)
     - Target mingguan (target harian × 7)
    */
    public function getWeeklyAnalytics($logs, $targetEnergy, $targetProtein, $targetFat)
    {
        $logs = collect($logs);

        // total nutrisi
        $totals = $this->calculateTotals($logs);

        // data chart (per tanggal)
        $chart = $logs->groupBy(function ($item) {
            return Carbon::parse($item->scanned_at)->format('Y-m-d');
        })->map(function ($items, $date) {
            return [
                'date' => $date,
                'energy' => $items->sum('energy'),
                'protein' => $items->sum('protein'),
                'fat' => $items->sum('fat'),
            ];
        })->values();

        // target mingguan
        $targets = [
            'energy' => $targetEnergy * 7,
            'protein' => $targetProtein * 7,
            'fat' => $targetFat * 7,
        ];

        return [
            'totals' => $totals,
            'chart' => $chart,
            'targets' => $targets
        ];
    }

    /* STATUS NUTRISI
     Menentukan kategori:
     - terpenuhi
     - cukup
        - kurang
    */
    public function getStatus($value, $target)
    {
        if ($value >= $target) return 'terpenuhi';
        if ($value >= $target * 0.7) return 'cukup';
        return 'kurang';
    }

    /*
    | Khusus lemak (ada kategori berlebih)
    */
    public function getFatStatus($value, $target)
    {
        if ($value > $target) return 'berlebih';
        if ($value >= $target * 0.7) return 'cukup';
        return 'kurang';
    }

    /*
    | Ambil status dari hasil weekly analytics
    */
    public function getNutritionStatuses($weekly)
    {
        $totals = $weekly['totals'] ?? [];
        $targets = $weekly['targets'] ?? [];

        return [
            'energy' => $this->getStatus(
                $totals['energy'] ?? 0,
                $targets['energy'] ?? 1
            ),
            'protein' => $this->getStatus(
                $totals['protein'] ?? 0,
                $targets['protein'] ?? 1
            ),
            'fat' => $this->getFatStatus(
                $totals['fat'] ?? 0,
                $targets['fat'] ?? 1
            ),
        ];
    }

    /*MONTHLY CALCULATION*/

    // kebutuhan bulanan = AKG harian × jumlah hari dalam bulan
    public function calculateMonthlyNeeds($akgDaily, $month, $year)
    {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        return [
            'energy' => $akgDaily['energy'] * $daysInMonth,
            'protein' => $akgDaily['protein'] * $daysInMonth,
            'fat' => $akgDaily['fat'] * $daysInMonth,
        ];
    }

    // total konsumsi selama sebulan (dari data harian)
    public function calculateMonthlyTotal($dailyData)
    {
        $total = ['energy' => 0, 'protein' => 0, 'fat' => 0];

        foreach ($dailyData as $day) {
            $total['energy'] += $day['energy'];
            $total['protein'] += $day['protein'];
            $total['fat'] += $day['fat'];
        }

        return $total;
    }

    // persentase pemenuhan nutrisi %
    public function calculatePercentage($total, $needs)
    {
        return [
            'energy' => $needs['energy'] ? ($total['energy'] / $needs['energy']) * 100 : 0,
            'protein' => $needs['protein'] ? ($total['protein'] / $needs['protein']) * 100 : 0,
            'fat' => $needs['fat'] ? ($total['fat'] / $needs['fat']) * 100 : 0,
        ];
    }

    /* DATA GROUPING (UNTUK CHART)*/

    // grouping per hari (line chart harian)
    public function groupByDate($logs)
    {
        return collect($logs)
            ->groupBy(function ($item) {
                return Carbon::parse($item->scanned_at)->format('Y-m-d');
            })
            ->map(function ($items, $date) {
                return [
                    'date' => $date,
                    'energy' => $items->sum('energy'),
                    'protein' => $items->sum('protein'),
                    'fat' => $items->sum('fat'),
                ];
            })
            ->values()
            ->toArray();
    }

    // grouping per minggu dalam 1 bulan (untuk chart bulanan)
    public function groupByWeek($dailyData)
    {
        return collect($dailyData)
            ->groupBy(function ($item) {
                return ceil(Carbon::parse($item['date'])->day / 7);
            })
            ->map(function ($items, $week) {

                return [

                    'week' => 'Minggu ' . $week,

                    'energy' => collect($items)->sum('energy'),
                    'protein' => collect($items)->sum('protein'),
                    'fat' => collect($items)->sum('fat'),

                    // DATA HARIAN
                    'energy_daily' => collect($items)->pluck('energy')->values(),

                    'protein_daily' => collect($items)->pluck('protein')->values(),

                    'fat_daily' => collect($items)->pluck('fat')->values(),
                ];
            })
            ->values()
            ->toArray();
    }

    /* MONTHLY ANALYSIS (FINAL)
    Menggabungkan:
    - kebutuhan
    - total konsumsi
    - persentase
    */
    public function getMonthlyAnalysis($dailyData, $akgDaily, $month, $year)
    {
        $needs = $this->calculateMonthlyNeeds($akgDaily, $month, $year);
        $total = $this->calculateMonthlyTotal($dailyData);
        $percentage = $this->calculatePercentage($total, $needs);

        return [
            'needs' => $needs,
            'total' => $total,
            'percentage' => $percentage,
        ];
    }

    // cleansing data chart sebelum di PDF
    public function cleanChartData($data)
{
    \Log::info('RAW DATA', [
        'data' => $data
    ]);

    $cleaned = collect($data)->map(function ($item) {

        return [

            'date' => $item['date'] ?? null,

            'energy' => (int) preg_replace(
                '/[^0-9]/',
                '',
                $item['energy'] ?? 0
            ),

            'protein' => (int) preg_replace(
                '/[^0-9]/',
                '',
                $item['protein'] ?? 0
            ),

            'fat' => (int) preg_replace(
                '/[^0-9]/',
                '',
                $item['fat'] ?? 0
            ),
        ];
    })->toArray();

    \Log::info('CLEANSED RESULT', [
        'data' => $cleaned
    ]);

    return $cleaned;
}
}