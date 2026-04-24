<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class ChildProfileController extends Controller
{
    public function index()
    {
        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_URL') . '/rest/v1/children', [
            'select' => '*'
        ]);

        $children = $response->json();

        return view('profile-balita', compact('children'));
    }
}