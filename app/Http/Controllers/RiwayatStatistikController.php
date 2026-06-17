<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RiwayatStatistikController extends Controller
{
        public function index()
    {
       // LOGIN CHECK
        if (!session()->has('user')) {

            return redirect('/login');
        }

        // ACTIVE CHILD
        $activeChildId = session('active_child_id');

        if (!$activeChildId) {

            return back()->with(
                'error',
                'Child tidak ditemukan'
            );
        }

        // REPORTS
        $reports = DB::connection('pgsql')
            ->table('monthly_reports')
            ->where('child_id', $activeChildId)
            ->latest('generated_at')
            ->get();

        return view(
            'riwayat-statistik',
            [

                'reports' => $reports,

            ]
        );
    }
}

