<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Services\SupabaseService;

class AuthController extends Controller
{
    private $supabase;


    public function login(Request $request)
    {
        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Content-Type' => 'application/json',
        ])
        ->post(env('SUPABASE_URL') . '/auth/v1/token?grant_type=password', [
            'email' => $request->email,
            'password' => $request->password,
        ]);

        $data = $response->json();

        if (isset($data['error'])) {
            return back()->with('login_error', $data['error_description'] ?? 'Login gagal');
        }

        session([
            'access_token' => $data['access_token'],
            'user' => $data['user'],
            'user_id' => $data['user']['id'], // 🔥 INI KUNCINYA
        ]);

        return redirect('/dashboard');
    }
}