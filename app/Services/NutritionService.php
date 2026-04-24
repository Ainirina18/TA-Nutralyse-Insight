<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Carbon\Carbon;

class NutritionService
{
    private SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    /*
    |------------------------------------------
    | 📊 WEEKLY SUMMARY
    |------------------------------------------
    */
    public function getWeeklySummary($userId)
    {
        $logs = collect($this->supabase->getWeeklyScanLogs($userId));

        return [
            'logs' => $logs,
            'totals' => $this->calculateTotals($logs)
        ];
    }

    /* ===== DAILY SUMMARY (FIXED VERSION) ===== */
    public function getDailySummary($userId)
    {
        $logs = collect($this->supabase->getDailyScanLogs($userId));

        return [
            'logs' => $logs,
            'totals' => $this->calculateTotals($logs)
        ];
    }

    /*
    |------------------------------------------
    | 🧮 TOTAL CALCULATION (GENERIC)
    |------------------------------------------
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
}