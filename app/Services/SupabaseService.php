<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SupabaseService
{
    private $url;
    private $key;

    public function __construct()
    {
        $this->url = env('SUPABASE_URL');
        $this->key = env('SUPABASE_ANON_KEY');
    }

    //  USER 
    public function getUserByEmail($email)
    {
        return Http::withHeaders([
            'apikey' => $this->key,
            'Authorization' => 'Bearer ' . $this->key,
        ])->get($this->url . '/rest/v1/users', [
            'email' => 'eq.' . $email
        ])->json();
    }

    // CORE FETCH FUNCTION
 
    private function fetchScanLogs($query)
    {
        return Http::withHeaders([
            'apikey' => $this->key,
            'Authorization' => 'Bearer ' . $this->key,
        ])->get($this->url . '/rest/v1/scan_logs' . $query)->json();
    }

        public function getChildrenByUser($userId, $token)
    {
        $response = Http::withHeaders([
            'apikey' => $this->key,
            'Authorization' => 'Bearer ' . $token,
        ])->get($this->url . '/rest/v1/children', [
            'user_id' => 'eq.' . $userId
        ]);

        return collect($response->json())->map(fn ($item) => (object) $item);
    }

    // DAILY LOGS BY CHILD

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

    // OPTIONAL: ALL LOGS BY CHILD //
    public function getAllScanLogsByChild($childId)
    {
        return $this->fetchScanLogs(
            "?child_id=eq.$childId&order=scanned_at.desc"
        );
    }

    public function getMonthlyScanLogsByChild($childId, $month, $year)
    {
        $startDate = "$year-$month-01 00:00:00";

        $endDate = date(
            'Y-m-t 23:59:59',
            strtotime($startDate)
        );

        $url = env('SUPABASE_URL')
            . "/rest/v1/scan_logs"
            . "?child_id=eq.$childId"
            . "&and=(scanned_at.gte.$startDate,scanned_at.lte.$endDate)"
            . "&order=scanned_at.asc";

        return Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . session('access_token'),
        ])->get($url)->json();
    }
}