<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Nutralyse Insight
    </title>

    <link
        rel="stylesheet"
        href="{{ asset('css/landing.css') }}"
    >

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

</head>

<body>

    {{-- ================= NAVBAR ================= --}}
    <header class="navbar">

        <div class="logo-wrapper">

            <img
                src="{{ asset('images/logo.png') }}"
                class="logo-img"
            >

            <div class="logo-text">

                <h2>Nutralyse</h2>

                <span>INSIGHT</span>

            </div>

        </div>

        <nav class="nav-menu">

            <a href="#">
                Beranda
            </a>

            <a href="#">
                Tentang Sistem
            </a>

            <a href="#">
                Fitur
            </a>

        </nav>

        <a
            href="/login"
            class="dashboard-btn"
        >

            Masuk ke Dashboard →

        </a>

    </header>

    {{-- ================= HERO ================= --}}
    <section class="hero-section">

        {{-- LEFT --}}
        <div class="hero-left">
            <h1>

                Pantau Nutrisi,
                <br>

                Pahami Perkembangan
                <br>

                Si Kecil dengan
                <span>Cerdas</span>

            </h1>

            <p>

                Nutralyse Insight adalah platform web yang membantu
                orang tua memantau asupan nutrisi, melihat evaluasi
                bulanan, dan mendapatkan insight cerdas untuk
                tumbuh kembang anak.

            </p>

            <a
                href="/login"
                class="hero-btn"
            >

                Masuk ke Dashboard →

            </a>

        </div>

        {{-- RIGHT --}}
        <div class="hero-right">

            <div class="preview-wrapper">

                <img
                    src="{{ asset('images/mockup-web.svg') }}"
                    class="dashboard-preview"
                >

            </div>

        </div>

    </section>

    {{-- ================= ECOSYSTEM ================= --}}
    <section class="ecosystem-section">

        <div class="section-badge">

            TERHUBUNG DALAM SATU EKOSISTEM

        </div>

        <h2>

            Terhubung Sempurna antara
            <br>
            Aplikasi Nutralyse Mobile dan Web

        </h2>

        <p class="ecosystem-desc">

            Data yang dicatat di aplikasi mobile akan otomatis
            tersinkronisasi ke web. Keduanya terhubung dengan
            akun yang sama, dalam satu ekosistem.

        </p>

        <div class="ecosystem-wrapper">

            {{-- MOBILE --}}
            <div class="ecosystem-card">

                <img
                    src="{{ asset('images/mockup-mobile.svg') }}"
                    class="ecosystem-image phone"
                >

                <div class="ecosystem-label">

                    Nutralyse Mobile App

                </div>

                <p>

                    Catat & pantau asupan harian
                    langsung dari genggaman.

                </p>

            </div>

            {{-- CENTER --}}
            <div class="sync-center">

                <div class="sync-circle">

                    ↻

                </div>

                <span>
                    Sinkronisasi Cloud
                </span>

            </div>

            {{-- WEB --}}
            <div class="ecosystem-card">

                <img
                    src="{{ asset('images/mockup-web.svg') }}"
                    class="ecosystem-image web"
                >

                <div class="ecosystem-label">

                    Nutralyse Insight Web

                </div>

                <p>

                    Lihat evaluasi, statistik,
                    dan insight nutrisi lebih lengkap.

                </p>

            </div>

        </div>

    </section>

</body>
</html>