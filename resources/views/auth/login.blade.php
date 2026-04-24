<x-guest-layout>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

<div class="login-container">

    <!-- Bagian Kiri -->
    <div class="login-left">
        <div class="left-content">
            <h1>Nutralyse-Insight</h1>
            <p>Pantau Nutrisi Makanan <br>Si Kecil Lebih Mudah</p>

            <img src="{{ asset('images/pic-login.png') }}" class="left-img">
        </div>
    </div>

    <!-- Bagian Kanan -->
    <div class="login-right">

        <div class="login-card">

            <h2>Selamat Datang!</h2>
            <div class="line-divider"></div>
            <p class="login-sub">
                Login menggunakan akun yang terdaftar pada aplikasi Nutralyse
            </p>

            <!-- Session Status -->
            <x-auth-session-status :status="session('status')" />

            <form method="POST" action="/login">
                @csrf

    <!-- EMAIL -->
        <div class="input-group">
        <img src="{{ asset('icons/email-icon.svg') }}" class="icon" alt="email icon">
        <input 
            class="login-input"
            type="email"
            name="email"
            placeholder="Email"
            value="{{ old('email') }}"
            required
            autofocus>
        </div>

    @error('email')
        <p class="error">{{ $message }}</p>
    @enderror


    <!-- PASSWORD -->
        <div class="input-group">
        <img src="{{ asset('icons/password-icon.svg') }}" class="icon" alt="password icon">
        <input 
            class="login-input"
            type="password"
            name="password"
            placeholder="Password"
            required>
        </div>

    @error('password')
        <p class="error">{{ $message }}</p>
    @enderror

                <!-- REMEMBER ME -->
                <div class="remember-box">
                    <label>
                        <input type="checkbox" name="remember">
                        Ingat saya di perangkat ini
                    </label>
                </div>

                <!-- LOGIN BUTTON -->
                <button class="btn">LOGIN</button>

            </form>
        </div>

    </div>

</div>
<x-modal-error-login />

</x-guest-layout>
