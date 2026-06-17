<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Nutralyse-Insight') }}</title>

    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile-balita.css')}}">
    <link rel="stylesheet" href="{{ asset('css/statistik-asupan.css')}}">
    <link rel="stylesheet" href="{{ asset('css/riwayat-statistik.css')}}">

    <!-- Google Font: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body style="font-family:'Poppins', sans-serif; margin:0;">

<div class="layout">

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar">

        <div class="sidebar-logo">
            <img src="{{ asset('images/logo.png') }}" class="logo-img">
        </div>
        
        <ul class="sidebar-menu">

           <li class="{{ request()->is('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}">
                <img src="{{ asset('icons/dashboard-icon.svg') }}" class="icon">
                <span>Beranda</span>
                </a>
            </li>

           <li class="{{ request()->is('statistik-asupan') ? 'active' : '' }}">
                <a href="{{ route('statistik.asupan') }}">
                    <img src="{{ asset('icons/statistik-icon.svg') }}" class="menu-icon">
                    <span>Statistik Asupan</span>
                </a>
            </li>

            <li class="{{ request()->is('riwayat-statistik') ? 'active' : '' }}">
                <a href="{{ route('riwayat.statistik') }}">
                    <img
                        src="{{ asset('icons/riwayat-icon.svg') }}"class="menu-icon">
                    <span>Riwayat Statistik</span>
                </a>
            </li>

           <li class="{{ request()->is('profile-balita') ? 'active' : '' }}">
                <a href="{{ url('/profile-balita') }}">
                    <img src="{{ asset('icons/setting-icon.svg') }}" class="menu-icon">
                    <span>Profil Balita</span>
                </a>
            </li>


        </ul>

        <div class="sidebar-footer">
            Nutralyse Insight © {{ date('Y') }}
        </div>

    </aside>


    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content">
        {{ $slot }}
    </div>

</div>

</body>
</html>
