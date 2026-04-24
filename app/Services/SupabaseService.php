<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class SupabaseService
{
    private $url;
    private $key;

    public function __construct()
    {
        $this->url = env('SUPABASE_URL');
        $this->key = env('SUPABASE_ANON_KEY');
    }

    // 👤 USER (optional)
    public function getUserByEmail($email)
    {
        return Http::withHeaders([
            'apikey' => $this->key,
            'Authorization' => 'Bearer ' . $this->key,
        ])->get($this->url . '/rest/v1/users', [
            'email' => 'eq.' . $email
        ])->json();
    }

    // =========================
    // 🔥 CORE FETCH FUNCTION
    // =========================
    private function fetchScanLogs($query)
    {
        return Http::withHeaders([
            'apikey' => $this->key,
            'Authorization' => 'Bearer ' . $this->key,
        ])->get($this->url . '/rest/v1/scan_logs' . $query)->json();
    }

    // =========================
    // 📊 DAILY LOGS BY CHILD
    // =========================
    public function getDailyScanLogsByChild($childId)
    {
        $start = Carbon::today()->startOfDay()->toDateTimeString();
        $end = Carbon::today()->endOfDay()->toDateTimeString();

        return $this->fetchScanLogs(
            "?child_id=eq.$childId"
            . "&scanned_at=gte.$start"
            . "&scanned_at=lte.$end"
            . "&order=scanned_at.desc"
        );
    }

    // =========================
    // 📈 WEEKLY LOGS BY CHILD
    // =========================
    public function getWeeklyScanLogsByChild($childId)
    {
        $start = Carbon::now()->subDays(6)->startOfDay()->toDateString();
        $end = Carbon::now()->endOfDay()->toDateString();

        return $this->fetchScanLogs(
            "?child_id=eq.$childId"
            . "&scanned_at=gte.$start"
            . "&scanned_at=lte.$end"
            . "&order=scanned_at.asc"
        );
    }

    // =========================
    // 👶 OPTIONAL: ALL LOGS BY CHILD
    // =========================
    public function getAllScanLogsByChild($childId)
    {
        return $this->fetchScanLogs(
            "?child_id=eq.$childId&order=scanned_at.desc"
        );
    }
}