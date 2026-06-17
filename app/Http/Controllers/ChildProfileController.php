<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class ChildProfileController extends Controller
{
    public function index()
    {
        if (!session()->has('user')) {
        return redirect('/login');
    }

        $userId = session('user')['id'] ?? null;

        //  children
        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_URL') . '/rest/v1/children', [
            'select' => '*',
        ]);

        $children = collect($response->json())
        ->map(fn ($item) => (object) $item);
        
        $activeChildId = session('active_child_id');

        $activeChild = $children->firstWhere(
            'id',
            $activeChildId
        );

        if (!$activeChild) {
            $activeChild = $children->first();
        }

        // parent
        $parent = null;

        if ($userId) {
            $parentResponse = Http::withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            ])->get(env('SUPABASE_URL') . '/rest/v1/parent_profiles', [
                'id' => 'eq.' . $userId,
                'select' => 'name',
            ]);

            $parent = collect($parentResponse->json())->first();
        }
        return view(
            'profile-balita',
            compact(
                'children',
                'parent',
                'activeChild'
            )
        );
    }
}