<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Services\SupabaseService;
use App\Services\NutritionService;

class RiwayatStatistikController extends Controller
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
        if (!session()->has('user')) {
            return redirect('/login');
        }

        $children = $this->supabase->getChildrenByUser(
            session('user')['id'],
            session('access_token')
        );

        if ($children->isEmpty()) {
            return back()->with('error', 'Data anak tidak ditemukan');
        }

        $activeChild = $this->nutrition->getActiveChild($children);

        if (!$activeChild) {
            return back()->with('error', 'Child tidak ditemukan');
        }

        $reports = DB::connection('pgsql')
            ->table('monthly_reports')
            ->where('child_id', $activeChild->id)
            ->latest('generated_at')
            ->get();

        return view('riwayat-statistik', [
            'reports' => $reports,
            'activeChild' => $activeChild,
        ]);
    }
}