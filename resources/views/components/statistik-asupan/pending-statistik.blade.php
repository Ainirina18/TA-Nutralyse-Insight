  <link rel="stylesheet" href="{{ asset('css/pending-statistik.css') }}">
<div class="pending-wrapper ">

   <x-mobile-topbar>

    <div class="topbar-left">

        <form method="GET" action="{{ route('statistik.asupan') }}">

            @include('components.widgets.month-dropdown')

        </form>

    </div>

    <div class="topbar-right">

        <button
            class="btn-export disabled"
            disabled
        >
            Export PDF
        </button>

    </div>

</x-mobile-topbar>

    {{-- HERO --}}
    <div class="pending-hero">

        <img
            src="{{ asset('images/STATISTIK-BULANAN.svg') }}"
            class="hero-img"
        >
   
        <div class="hero-text">

            <h1>
                Jurnal Hebat Si Kecil:
            </h1>

            <h2>
                Edisi
                {{ \Carbon\Carbon::create()->month((int)$month)->translatedFormat('F') }}
            </h2>

        </div>

    </div>

    {{-- MAIN PENDING CARD --}}
    <div class="pending-card">

        <div class="pending-left">

            <h3>
                Bulan Ini Belum Tuntas!
            </h3>

            <p>
                Evaluasi dan laporan lengkap baru tersedia setelah akhir bulan.
            </p>

            <p>
                Terus lakukan scan makanan setiap hari agar data asupan lebih akurat dan rekomendasi lebih tepat untuk ananda.
            </p>

        </div>

        <div class="pending-right">

            <img
                src="{{ asset('images/pending-page-image.svg') }}"
                class="pending-img"
            >

        </div>

        {{-- REMINDER --}}
        <div class="pending-reminder">

            <img
                src="{{ asset('images/reminder-statistik-pending.svg') }}"
                class="reminder-icon"
            >

            <span>
                Data hari ini akan tetap kami simpan dan digunakan untuk evaluasi pada akhir bulan.
            </span>

        </div>

    </div>

    {{-- HISTORY --}}
    <div class="history-section">

        <h3 class="history-title">
            Laporan Baru Saja Dibuka
        </h3>

        <div class="history-grid">

            @foreach($historyReports as $report)

                <div class="history-card">

                    {{-- TOP --}}
                    <div class="history-top">

                        <div class="history-icon">

                            <img
                                src="{{ asset('icons/calendar-icon-pending.svg') }}"
                            >

                        </div>

                        <div class="history-info">

                            <h4>
                                {{ \Carbon\Carbon::create()
                                    ->month($report->report_month)
                                    ->translatedFormat('F') }}
                                {{ $report->report_year }}
                            </h4>

                            <span class="history-status">
                                selesai
                            </span>

                        </div>

                    </div>

                    {{-- TEXT --}}
                    <p>
                        evaluasi dibuat pada
                        <br>
                        {{ \Carbon\Carbon::parse($report->generated_at)->translatedFormat('j F Y') }}
                    </p>

                    {{-- BUTTON --}}
                    <a
                        href="{{ route('statistik.asupan', [
                            'month_year' =>
                            $report->report_year . '-' .
                            str_pad($report->report_month, 2, '0', STR_PAD_LEFT)
                        ]) }}"
                        class="history-btn"
                    >

                        Lihat Laporan

                    </a>

                </div>

            @endforeach

        </div>

    </div>

    {{-- BOTTOM INFO --}}
    <div class="bottom-info-card">

        <div class="bottom-icon">

            <img
                src="{{ asset('icons/statistik-icon-pending.svg') }}"
            >

        </div>

        <div class="bottom-content">

            <h4>
                Evaluasi Bulanan Membantu Memantau Perkembangan Nutrisi Ananda Secara Lebih Akurat Dan Menyeluruh
            </h4>

            <p>
                Terimakasih sudah rutin melakukan scan makanan setiap hari
            </p>

        </div>

    </div>

</div>
