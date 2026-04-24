<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\SupabaseService;
use App\Services\NutritionService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private SupabaseService $supabase;
    private NutritionService $nutrition;

    public function __construct(
        SupabaseService $supabase,
        NutritionService $nutrition
    ) {
        $this->supabase = $supabase;
        $this->nutrition = $nutrition;
    }

    public function index()
    {
        // 🔐 LOGIN CHECK
        if (!session()->has('user')) {
            return redirect('/login');
        }

        $userId = session('user')['id'] ?? null;
        $token = session('access_token');

        if (!$userId || !$token) {
            return redirect('/login');
        }

        // 👶 GET CHILDREN
        $childResponse = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . $token,
        ])->get(env('SUPABASE_URL') . '/rest/v1/children', [
            'user_id' => 'eq.' . $userId
        ]);

        $children = collect($childResponse->json());

        if ($children->isEmpty()) {
            return back()->with('error', 'Data anak tidak ditemukan');
        }

        // 🧠 ACTIVE CHILD
        $activeChildId = session('active_child_id') ?? $children->first()['id'];

        $activeChild = $children->firstWhere('id', $activeChildId);

        if (!$activeChild) {
            $activeChild = $children->first();
            $activeChildId = $activeChild['id'];
            session(['active_child_id' => $activeChildId]);
        }

        // 🎯 AGE
        $userAge = (float) ($activeChild['age'] ?? 0);

        // 🎯 AKG TARGET
        $akg = DB::connection('pgsql')
            ->table('master_akg')
            ->where('usia_min', '<=', $userAge)
            ->where('usia_max', '>=', $userAge)
            ->first();

        $targetEnergy = (float) ($akg->energi ?? 0);
        $targetProtein = (float) ($akg->protein ?? 0);
        $targetFat = (float) ($akg->lemak ?? 0);

        // 📊 SCAN LOGS
        $dailyLogs = collect(
            $this->supabase->getDailyScanLogsByChild($activeChildId)
        )->map(fn ($item) => (object) $item);

        $weeklyLogs = collect(
            $this->supabase->getWeeklyScanLogsByChild($activeChildId)
        )->map(fn ($item) => (object) $item);

        // 🧮 DAILY TOTAL
        $dailyTotals = $this->nutrition->calculateTotals($dailyLogs);

        // 🧮 WEEKLY TOTAL
        $weeklyTotals = [
            'energy' => $weeklyLogs->sum('energy'),
            'protein' => $weeklyLogs->sum('protein'),
            'fat' => $weeklyLogs->sum('fat'),
        ];

        // 📈 WEEKLY CHART DATA
        $weeklyData = $weeklyLogs->groupBy(function ($item) {
            return Carbon::parse($item->scanned_at)->format('Y-m-d');
        })->map(function ($items, $date) {
            return [
                'date' => $date,
                'energy' => $items->sum('energy'),
                'protein' => $items->sum('protein'),
                'fat' => $items->sum('fat'),
            ];
        })->values();

        // 🔴 DAILY STATUS
        $energy = $dailyTotals['energy'] ?? 0;
        $protein = $dailyTotals['protein'] ?? 0;
        $fat = $dailyTotals['fat'] ?? 0;

        $weeklyTargetEnergy = $targetEnergy * 7;
        $weeklyTargetProtein = $targetProtein * 7;
        $weeklyTargetFat = $targetFat * 7;

        return view('dashboard', [
            // 👶 CHILD
            'children' => $children,
            'activeChildId' => $activeChildId,
            'activeChild' => $activeChild,

            // 📊 DAILY
            'lmhiLogs' => $dailyLogs,
            'energy' => $energy,
            'protein' => $protein,
            'fat' => $fat,

            // 📈 WEEKLY
            'weeklyLogs' => $weeklyLogs,
            'weeklyEnergy' => $weeklyTotals['energy'],
            'weeklyProtein' => $weeklyTotals['protein'],
            'weeklyFat' => $weeklyTotals['fat'],
            'weeklyData' => $weeklyData,

            // 🎯 TARGET
            'targetEnergy' => $targetEnergy,
            'targetProtein' => $targetProtein,
            'targetFat' => $targetFat,

            //TARGET WEEKLY
            'weeklyTargetEnergy' => $weeklyTargetEnergy,
            'weeklyTargetProtein' => $weeklyTargetProtein,
            'weeklyTargetFat' => $weeklyTargetFat,
            
            // 🔻 SISA (optional kalau dipakai UI)
            'sisaEnergy' => max(0, $targetEnergy - $energy),
            'sisaProtein' => max(0, $targetProtein - $protein),
            'sisaFat' => max(0, $targetFat - $fat),
        ]);
    }

    public function setChild(Request $request)
    {
        session(['active_child_id' => $request->child_id]);
        return redirect()->route('dashboard');
    }
    
}