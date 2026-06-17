<x-guest-layout>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

<div class="login-container">

    {{-- ================= LEFT ================= --}}
    <div class="login-left">

        {{-- overlay blur --}}
        <div class="left-overlay"></div>
        <svg
            class="wave-shape"
            viewBox="0 0 140 1000"
            preserveAspectRatio="none"
        >

            <path
                d="
                M140,0
                C20,140 20,320 110,500
                C180,700 60,860 140,1000
                L140,1000
                L140,0
                Z
                "
                fill="#F7F5F1"
            />

        </svg>

        {{-- content --}}
        <div class="left-content">

            <div class="brand-badge">

                NUTRALYSE INSIGHT WEB

            </div>

            <h1>

                Pantau Nutrisi
                <br>
                Si Kecil Lebih
                <br>
                Mudah & Cerdas

            </h1>

            <p>

                Platform evaluasi nutrisi berbasis AI
                untuk membantu orang tua memahami
                perkembangan asupan harian anak.

            </p>

            <img
                src="{{ asset('images/pic-login.png') }}"
                class="left-img"
            >

        </div>

    </div>

    {{-- ================= RIGHT ================= --}}
    <div class="login-right">

        <div class="login-card">

            <h2>

                Masuk ke Dashboard

            </h2>

            <p class="login-sub">

                Login menggunakan akun yang
                terdaftar pada aplikasi Nutralyse.

            </p>

            {{-- SESSION STATUS --}}
            <x-auth-session-status :status="session('status')" />

            <form
                method="POST"
                action="/login"
            >

                @csrf

                {{-- EMAIL --}}
                <div class="input-group">

                    <img
                        src="{{ asset('icons/email-icon.svg') }}"
                        class="icon"
                        alt="email"
                    >

                    <input
                        class="login-input"
                        type="email"
                        name="email"
                        placeholder="Masukkan email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                    >

                </div>

                @error('email')

                    <p class="error">

                        {{ $message }}

                    </p>

                @enderror

                {{-- PASSWORD --}}
                <div class="input-group">

                    <img
                        src="{{ asset('icons/password-icon.svg') }}"
                        class="icon"
                        alt="password"
                    >

                    <input
                        class="login-input"
                        type="password"
                        name="password"
                        placeholder="Masukkan password"
                        required
                    >

                </div>

                @error('password')

                    <p class="error">

                        {{ $message }}

                    </p>

                @enderror

                {{-- REMEMBER --}}
                <div class="remember-box">

                    <label>

                        <input
                            type="checkbox"
                            name="remember"
                        >

                        Ingat saya di perangkat ini

                    </label>

                </div>

                {{-- BUTTON --}}
                <button class="btn">

                    Masuk ke Dashboard →

                </button>

            </form>

        </div>

    </div>

</div>

<x-modal-error-login />

</x-guest-layout>