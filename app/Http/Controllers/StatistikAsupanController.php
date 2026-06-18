<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SupabaseService;
use App\Services\NutritionService;
use Illuminate\Support\Facades\DB;
use App\Services\GeminiService;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Http;

class StatistikAsupanController extends Controller
{
    private SupabaseService $supabase;
    private NutritionService $nutrition;
    private GeminiService $gemini;

    public function __construct(
        SupabaseService $supabase,
        NutritionService $nutrition,
        GeminiService $gemini
    ) {
        $this->supabase = $supabase;
        $this->nutrition = $nutrition;
        $this->gemini = $gemini;
    }

    public function index(Request $request)
    {
        $monthYear = $request->month_year ?? now()->format('Y-m');
        [$year, $month] = explode('-', $monthYear);

        $month = (int) $month;
        $year = (int) $year;

        $isCurrentMonth =
            $month == (int) now()->format('m') &&
            $year == (int) now()->format('Y');

        if (!session()->has('user')) {
            return redirect('/login');
        }

        // ambil children
        $children = $this->supabase->getChildrenByUser(
            session('user')['id'],
            session('access_token')
        );

        if ($children->isEmpty()) {
            return back()->with('error', 'Data anak tidak ditemukan');
        }

        // active child
        $activeChild = $this->nutrition->getActiveChild($children);

        if (!$activeChild) {
            return back()->with('error', 'Child tidak ditemukan');
        }

        $activeChildId = $activeChild->id;

        // AKG
        $targets = $this->nutrition->getAKGTargets($activeChild);

        $targetEnergy = $targets['energy'];
        $targetProtein = $targets['protein'];
        $targetFat = $targets['fat'];

        $akgDaily = [
            'energy' => $targetEnergy,
            'protein' => $targetProtein,
            'fat' => $targetFat,
        ];

        // logs
        $monthlyLogs = collect(
            $this->supabase->getMonthlyScanLogsByChild(
                $activeChildId,
                $month,
                $year
            )
        )
            ->map(fn ($item) => (object) $item)
            ->filter(function ($item) {
                $scanDate = \Carbon\Carbon::parse($item->scanned_at);

                return $scanDate->between(
                    now()->subYear(),
                    now()
                );
            });

        $hasScanLogs = $monthlyLogs->isNotEmpty();

        // report bulan yang sedang dibuka
        $existingReport = DB::connection('pgsql')
            ->table('monthly_reports')
            ->where('child_id', $activeChildId)
            ->where('report_month', $month)
            ->where('report_year', $year)
            ->latest('generated_at')
            ->first();

        $hasReport = $existingReport ? true : false;

        // riwayat report selain bulan yang sedang dibuka
        $historyReports = DB::connection('pgsql')
            ->table('monthly_reports')
            ->where('child_id', $activeChildId)
            ->where(function ($query) use ($month, $year) {
                $query->where('report_month', '!=', $month)
                    ->orWhere('report_year', '!=', $year);
            })
            ->latest('generated_at')
            ->limit(3)
            ->get();

        // bulan berjalan selalu pending
        if ($isCurrentMonth) {
            return view('statistik-asupan', [
                'pageState' => 'pending',
                'month' => $month,
                'year' => $year,
                'historyReports' => $historyReports,
                'hasReport' => $hasReport,
            ]);
        }

        // bulan lama tanpa scan logs sama sekali tampil empty
        if (!$hasScanLogs) {
            return view('statistik-asupan', [
                'pageState' => 'empty',
                'month' => $month,
                'year' => $year,
                'historyReports' => $historyReports,
                'hasReport' => false,
            ]);
        }

        // kalau report sudah ada dan scan logs juga ada, tampilkan report
        if ($existingReport) {
            return view('statistik-asupan', [
                'isCurrentMonth' => $isCurrentMonth,
                'historyReports' => $historyReports,
                'hasReport' => $hasReport,

                'dailyChart' => json_decode($existingReport->daily_chart, true),
                'weeklyChart' => json_decode($existingReport->weekly_chart, true),

                'analysis' => [
                    'total' => [
                        'energy' => $existingReport->total_energy,
                        'protein' => $existingReport->total_protein,
                        'fat' => $existingReport->total_fat,
                    ],
                    'needs' => [
                        'energy' => $existingReport->target_energy,
                        'protein' => $existingReport->target_protein,
                        'fat' => $existingReport->target_fat,
                    ],
                    'percentage' => [
                        'energy' => $existingReport->energy_percentage,
                        'protein' => $existingReport->protein_percentage,
                        'fat' => $existingReport->fat_percentage,
                    ],
                ],

                'nutritionJourney' => $existingReport->nutrition_journey,
                'nutritionGap' => $existingReport->nutrition_gap,
                'nextMonthStrategy' => $existingReport->next_month_strategy,
                'parentMotivation' => $existingReport->parent_motivation,

                'missionTitle' => $existingReport->mission_title,
                'missionContent' => $existingReport->mission_content,

                'month' => $month,
                'year' => $year,

                'targetEnergy' => $targetEnergy,
                'targetProtein' => $targetProtein,
                'targetFat' => $targetFat,
            ]);
        }

        $dailyData = $this->nutrition->groupByDate($monthlyLogs);
        $dailyData = $this->nutrition->cleanChartData($dailyData);

        $weeklyData = $this->nutrition->groupByWeek($dailyData);
        $weeklyData = $this->nutrition->cleanChartData($weeklyData);

        $analysis = $this->nutrition->getMonthlyAnalysis(
            $dailyData,
            $akgDaily,
            $month,
            $year
        );

        $analysis['allergy'] =
            $activeChild->allergy ?? 'Tidak ada';

        try {
            $aiResponse =
                $this->gemini->generateMonthlyEvaluation($analysis);

            $text =
                $aiResponse['candidates'][0]['content']['parts'][0]['text']
                ?? '';
        } catch (\Exception $e) {
            $text = '';
        }

        try {
            $missionResponse =
                $this->gemini->generateMonthlyMission($analysis);

            $missionText =
                $missionResponse['candidates'][0]['content']['parts'][0]['text']
                ?? '';
        } catch (\Exception $e) {
            $missionText = '';
        }

        preg_match('/===PERJALANAN_NUTRISI===\s*(.*?)\s*===EVALUASI_CELAH===/s', $text, $journey);
        preg_match('/===EVALUASI_CELAH===\s*(.*?)\s*===STRATEGI_MENU===/s', $text, $gap);
        preg_match('/===STRATEGI_MENU===\s*(.*?)\s*===MOTIVASI_PARENT===/s', $text, $strategy);
        preg_match('/===MOTIVASI_PARENT===\s*(.*)/s', $text, $motivation);

        preg_match('/===MISSION_TITLE===\s*(.*?)\s*===MISSION_CONTENT===/s', $missionText, $missionTitle);
        preg_match('/===MISSION_CONTENT===\s*(.*)/s', $missionText, $missionContent);

        $alreadyExists = DB::connection('pgsql')
            ->table('monthly_reports')
            ->where('child_id', $activeChildId)
            ->where('report_month', $month)
            ->where('report_year', $year)
            ->exists();

        if (!$alreadyExists) {
            DB::connection('pgsql')
                ->table('monthly_reports')
                ->insert([
                    'user_id' => session('user')['id'],
                    'child_id' => $activeChildId,

                    'report_month' => $month,
                    'report_year' => $year,

                    'total_energy' => $analysis['total']['energy'],
                    'total_protein' => $analysis['total']['protein'],
                    'total_fat' => $analysis['total']['fat'],

                    'target_energy' => $analysis['needs']['energy'],
                    'target_protein' => $analysis['needs']['protein'],
                    'target_fat' => $analysis['needs']['fat'],

                    'energy_percentage' => $analysis['percentage']['energy'],
                    'protein_percentage' => $analysis['percentage']['protein'],
                    'fat_percentage' => $analysis['percentage']['fat'],

                    'daily_chart' => json_encode($dailyData),
                    'weekly_chart' => json_encode($weeklyData),

                    'nutrition_journey' => trim($journey[1] ?? ''),
                    'nutrition_gap' => trim($gap[1] ?? ''),
                    'next_month_strategy' => trim($strategy[1] ?? ''),
                    'parent_motivation' => trim($motivation[1] ?? ''),

                    'mission_title' => trim($missionTitle[1] ?? ''),
                    'mission_content' => trim($missionContent[1] ?? ''),

                    'generated_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        $existingReport = DB::connection('pgsql')
            ->table('monthly_reports')
            ->where('child_id', $activeChildId)
            ->where('report_month', $month)
            ->where('report_year', $year)
            ->latest('generated_at')
            ->first();

        $hasReport = $existingReport ? true : false;

        return view('statistik-asupan', [
            'isCurrentMonth' => $isCurrentMonth,
            'historyReports' => $historyReports,
            'hasReport' => $hasReport,

            'dailyChart' => json_decode($existingReport->daily_chart, true),
            'weeklyChart' => json_decode($existingReport->weekly_chart, true),
            'analysis' => $analysis,

            'nutritionJourney' => trim($journey[1] ?? ''),
            'nutritionGap' => trim($gap[1] ?? ''),
            'nextMonthStrategy' => trim($strategy[1] ?? ''),
            'parentMotivation' => trim($motivation[1] ?? ''),

            'missionTitle' => trim($missionTitle[1] ?? ''),
            'missionContent' => trim($missionContent[1] ?? ''),

            'month' => $month,
            'year' => $year,

            'targetEnergy' => $targetEnergy,
            'targetProtein' => $targetProtein,
            'targetFat' => $targetFat,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $monthYear = $request->month_year ?? now()->format('Y-m');

        [$year, $month] = explode('-', $monthYear);

        $month = (int) $month;
        $year = (int) $year;

        if (!session()->has('user')) {
            return redirect('/login');
        }

        $children = $this->supabase->getChildrenByUser(
            session('user')['id'],
            session('access_token')
        );

        $activeChild = $this->nutrition->getActiveChild($children);

        $parentResponse = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_URL') . '/rest/v1/parent_profiles', [
            'id' => 'eq.' . $activeChild->user_id,
            'select' => 'name',
        ]);

        $parent = collect($parentResponse->json())->first();

        $report = DB::connection('pgsql')
            ->table('monthly_reports')
            ->where('child_id', $activeChild->id)
            ->where('report_month', $month)
            ->where('report_year', $year)
            ->latest('generated_at')
            ->first();

        if (!$report) {
            return back()->with('error', 'Laporan tidak ditemukan');
        }

        $analysis = [
            'total' => [
                'energy' => $report->total_energy ?? 0,
                'protein' => $report->total_protein ?? 0,
                'fat' => $report->total_fat ?? 0,
            ],

            'needs' => [
                'energy' => $report->target_energy ?? 0,
                'protein' => $report->target_protein ?? 0,
                'fat' => $report->target_fat ?? 0,
            ],

            'percentage' => [
                'energy' => $report->energy_percentage ?? 0,
                'protein' => $report->protein_percentage ?? 0,
                'fat' => $report->fat_percentage ?? 0,
            ],
        ];

        $html = view('pdf.statistik-asupan-pdf', [
            'report' => $report,
            'child' => $activeChild,
            'parent' => $parent,
            'analysis' => $analysis,

            'month' => $month,
            'year' => $year,

            'energyChart' => $request->energy_chart,
            'proteinChart' => $request->protein_chart,
            'fatChart' => $request->fat_chart,

            'dailyCharts' => json_decode($request->daily_charts, true),
            'weeklyCharts' => json_decode($request->weekly_charts, true),
        ])->render();

        $pdfPath = storage_path('app/public/laporan-statistik.pdf');

        Browsershot::html($html)
            ->format('A4')
            ->showBackground()
            ->margins(0, 0, 0, 0)
            ->savePdf($pdfPath);

        return response()->download($pdfPath);
    }
}