<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\SupabaseService;
use App\Services\NutritionService;
use App\Services\GeminiService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class DashboardController extends Controller
{
    private SupabaseService $supabase;
    private NutritionService $nutrition;
    private GeminiService $gemini;

    public function __construct(
        SupabaseService $supabase,
        NutritionService $nutrition,
        GeminiService $gemini,
    ) {
        $this->supabase = $supabase;
        $this->nutrition = $nutrition;
        $this->gemini = $gemini;
    }

    public function index()
    {
        //  LOGIN CHECK
        if (!session()->has('user')) {
            return redirect('/login');
        }

        $userId = session('user')['id'] ?? null;
        $token = session('access_token');

        if (!$userId || !$token) {
            return redirect('/login');
        }

        //  GET CHILDREN
        $children = collect(
            $this->supabase->getChildrenByUser($userId, $token)
        )->map(fn ($item) => (object) $item);

        if ($children->isEmpty()) {
            return back()->with('error', 'Data anak tidak ditemukan');
        }

      //  active child
        $activeChild = $this->nutrition->getActiveChild($children);

        if (!$activeChild) {
            return back()->with('error', 'Child tidak ditemukan');
        }

        $activeChildId = $activeChild->id;

        //  AKG dari service
        $targets = $this->nutrition->getAKGTargets($activeChild);

        $targetEnergy = $targets['energy'];
        $targetProtein = $targets['protein'];
        $targetFat = $targets['fat'];

        //  logs
        $dailyLogs = collect(
            $this->supabase->getDailyScanLogsByChild($activeChildId)
        )->map(fn ($item) => (object) $item);

        $weeklyLogs = collect(
            $this->supabase->getWeeklyScanLogsByChild($activeChildId)
        )->map(fn ($item) => (object) $item);

        // analytics
        $daily = $this->nutrition->getDailyAnalytics(
            $dailyLogs,
            $targetEnergy,
            $targetProtein,
            $targetFat
        );

        $weekly = $this->nutrition->getWeeklyAnalytics(
            $weeklyLogs,
            $targetEnergy,
            $targetProtein,
            $targetFat
        );

    $statuses = $this->nutrition->getNutritionStatuses($weekly);

    $energyStatus = $statuses['energy'];
    $proteinStatus = $statuses['protein'];
    $fatStatus = $statuses['fat'];

   // ================= EARLY WARNING =================

    $warningData = [

        'energy_percentage' =>
            round(($weekly['totals']['energy'] / $weekly['targets']['energy']) * 100),

        'protein_percentage' =>
            round(($weekly['totals']['protein'] / $weekly['targets']['protein']) * 100),

        'fat_percentage' =>
            round(($weekly['totals']['fat'] / $weekly['targets']['fat']) * 100),
    ];

    // ambil tanggal scan pertama
    $firstScanDate =
        collect($weeklyLogs)->min('scanned_at');

    // hitung hari berjalan
    $daysPassed =
        \Carbon\Carbon::parse($firstScanDate)
        ->diffInDays(now());

    // minggu evaluasi AI
    $currentWeek =
        ceil(($daysPassed + 1) / 7);

    // trigger tiap akhir minggu
    $isEndOfWeek =
        ($daysPassed + 1) % 7 == 0;

    // ambil warning terakhir
    $latestWarning = DB::connection('pgsql')
        ->table('early_warnings')
        ->where('child_id', $activeChildId)
        ->orderByDesc('created_at')
        ->first();

    if ($isEndOfWeek) {

        // cek apakah minggu ini sudah pernah generate
        $alreadyGenerated = DB::connection('pgsql')
            ->table('early_warnings')
            ->where('child_id', $activeChildId)
            ->where('warning_week', $currentWeek)
            ->where('warning_month', now()->month)
            ->where('warning_year', now()->year)
            ->exists();

        if (!$alreadyGenerated) {

            try {

                $response =
                    $this->gemini->generateWeeklyWarning($warningData);

                $earlyWarning =
                    $response['candidates'][0]['content']['parts'][0]['text']
                    ?? 'Belum ada evaluasi.';

                // simpan ke database
                DB::connection('pgsql')
                    ->table('early_warnings')
                    ->insert([

                        'user_id' => $userId,

                        'child_id' => $activeChildId,

                        'warning_week' => $currentWeek,

                        'warning_month' => now()->month,

                        'warning_year' => now()->year,

                        'warning_text' => $earlyWarning,

                        'created_at' => now(),
                    ]);

            } catch (\Exception $e) {

                $earlyWarning =
                    $latestWarning->warning_text
                    ?? 'Evaluasi AI sedang tidak tersedia.';
            }

        } else {

            $earlyWarning =
                $latestWarning->warning_text
                ?? 'Evaluasi mingguan belum tersedia.';
        }

    } else {

        $earlyWarning =
            $latestWarning->warning_text
            ?? 'Evaluasi mingguan belum tersedia.';
    }


        return view('dashboard', [
        // CHILD
        'children' => $children,
        'activeChildId' => $activeChildId,
        'activeChild' => $activeChild,

        // DAILY
        'lmhiLogs' => $dailyLogs,
        'energy' => $daily['totals']['energy'],
        'protein' => $daily['totals']['protein'],
        'fat' => $daily['totals']['fat'],

        // WEEKLY
        'weeklyLogs' => $weeklyLogs,
        'weeklyEnergy' => $weekly['totals']['energy'],
        'weeklyProtein' => $weekly['totals']['protein'],
        'weeklyFat' => $weekly['totals']['fat'],
        'weeklyData' => $weekly['chart'],

        // TARGET
        'targetEnergy' => $targetEnergy,
        'targetProtein' => $targetProtein,
        'targetFat' => $targetFat,

        // WEEKLY TARGET
        'weeklyTargetEnergy' => $weekly['targets']['energy'],
        'weeklyTargetProtein' => $weekly['targets']['protein'],
        'weeklyTargetFat' => $weekly['targets']['fat'],

        // SISA
        'sisaEnergy' => $daily['sisa']['energy'],
        'sisaProtein' => $daily['sisa']['protein'],
        'sisaFat' => $daily['sisa']['fat'],

        //EARLY WARNING
        'earlyWarning' => $earlyWarning,
        ]);
    }

    public function setChild(Request $request)
    {
        session(['active_child_id' => $request->child_id]);
        return redirect()->route('dashboard');
    }
    
}