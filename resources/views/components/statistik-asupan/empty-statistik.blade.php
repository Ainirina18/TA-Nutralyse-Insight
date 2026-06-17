<link
    rel="stylesheet"
    href="{{ asset('css/pending-statistik.css') }}"
>

<div class="empty-wrapper">

    {{-- HERO --}}
    <div class="pending-hero">

        <img
            src="{{ asset('images/STATISTIK-BULANAN.svg') }}"
            class="hero-img"
        >

        <div class="hero-text">

            <h1>Jurnal Hebat Si Kecil:</h1>

            <h2>
                Edisi
                {{ \Carbon\Carbon::create()
                    ->month((int)$month)
                    ->translatedFormat('F') }}
            </h2>

        </div>

    </div>

    {{-- EMPTY CONTENT --}}
    <div class="empty-content">

        <img
            src="{{ asset('images/empty.svg') }}"
            class="empty-img"
        >

        <h3>
            Belum ada laporan nutrisi
        </h3>

        <p>
            Belum ditemukan data scan pada bulan ini,
            sehingga laporan evaluasi tidak bisa dibuat.
        </p>

        <a
            href="{{ route('statistik.asupan') }}"
            class="empty-btn"
        >

            Lihat Bulan Lain

        </a>

    </div>

</div>