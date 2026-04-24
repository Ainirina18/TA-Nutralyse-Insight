<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use App\Models\UserWeb;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $email = $request->email;
        $password = $request->password;

        // Request ke Supabase REST API
        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_KEY_ANON'),
            'Authorization' => 'Bearer '.env('SUPABASE_KEY_ANON'),
            'Content-Type' => 'application/json'
        ])->get(env('SUPABASE_URL').'/rest/v1/users', [
            'email' => 'eq.'.$email
        ]);

        $users = $response->json();

        // Jika user tidak ditemukan
        if (!$users || count($users) === 0) {
            return back()->withErrors([
                'email' => 'Akun tidak ditemukan'
            ]);
        }

        $user = $users[0];

        // Cek password
        if (password_verify($password, $user['password'])) {

            // Simpan/update user di DB web
            $userWeb = UserWeb::updateOrCreate(
                ['supabase_user_id' => $user['id']], // kunci unik
                [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'last_login' => now()
                ]
            );

            // Session pakai ID DB web
            Session::put('user_id', $userWeb->id);
            Session::put('user_name', $userWeb->name);

            // Regenerate session Laravel
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard', absolute: false));
        } else {
            return back()->withErrors([
                'password' => 'Password salah'
            ]);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}