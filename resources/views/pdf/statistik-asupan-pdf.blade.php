<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <title>Laporan Statistik Asupan</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ public_path('css/statistik-asupan-pdf.css') }}">
</head>

<body>

    {{-- ================= PAGE 1 ================= --}}
    <section class="pdf-page">

        {{-- HEADER --}}
        <div class="pdf-header">

            <img
                src="{{ public_path('images/pdf/logo.png') }}"
                class="header-icon"
            >

            <div class="header-text">

                <h1>Jurnal Hebat Si Kecil:</h1>

                <h2>
                    Edisi
                    {{ \Carbon\Carbon::create()->month((int)$month)->translatedFormat('F') }}
                </h2>

            </div>

        </div>

        {{-- PROFILE --}}
        <div class="child-profile">

            <h3>
                {{ $child->name ?? '-' }}
            </h3>

            <div class="profile-grid">

                <div class="profile-column">

                    <p>
                        <strong>Gender :</strong>
                        {{ $child->gender ?? '-' }}
                    </p>

                    <p>
                        <strong>Umur :</strong>
                        {{ $child->age ?? '-' }}
                    </p>

                    <p>
                        <strong>Ayah & Bunda :</strong>
                        {{ $parent['name'] ?? '-' }}
                    </p>

                </div>

                <div class="profile-column">

                    <p>
                        <strong>Berat Badan :</strong>
                        {{ $child->weight ?? '-' }}
                    </p>

                    <p>
                        <strong>Tinggi Badan :</strong>
                        {{ $child->height ?? '-' }}
                    </p>

                    <p>
                        <strong>Alergi :</strong>
                        {{ $child->allergy ?? '-' }}
                    </p>

                </div>

            </div>

        </div>

        {{-- SECTION TITLE --}}
        <h4 class="section-title">
            Asupan Nutrisi Ananda Bulan Ini
        </h4>

        {{-- CHART GRID --}}
        <div class="chart-grid">

            <div class="chart-card">

                <img
                    src="{{ $dailyCharts['energy'] ?? '' }}"
                    class="chart-image"
                >

            </div>

            <div class="chart-card">

                <img
                    src="{{ $dailyCharts['protein'] ?? '' }}"
                    class="chart-image"
                >

            </div>

            <div class="chart-card">

                <img
                    src="{{ $dailyCharts['fat'] ?? '' }}"
                    class="chart-image"
                >

            </div>

        </div>

        {{-- TOTAL NUTRISI --}}
        <div class="nutrition-total">

            <h4>Total Nutrisi Bulan Ini</h4>

            <div class="nutrition-grid">

                <div class="nutrition-item">

                    <span>Energi</span>

                    <strong>
                        {{ number_format($analysis['total']['energy'], 0) }} kkal
                    </strong>

                </div>

                <div class="nutrition-item">

                    <span>Protein</span>

                    <strong>
                        {{ number_format($analysis['total']['protein'], 0) }} g
                    </strong>

                </div>

                <div class="nutrition-item">

                    <span>Lemak</span>

                    <strong>
                        {{ number_format($analysis['total']['fat'], 0) }} g
                    </strong>

                </div>

            </div>

        </div>

        {{-- AI EVALUATION --}}
        <div class="evaluation-block">

            <h4>
                Evaluasi Perjalanan Nutrisi Bulan Ini
            </h4>

            <p>
                {{ $report->nutrition_journey }}
            </p>

        </div>

        <div class="evaluation-block">

            <h4>
                Evaluasi Celah Nutrisi Bulanan
            </h4>

            <p>
                {{ $report->nutrition_gap }}
            </p>

        </div>

    </section>

    {{-- ================= PAGE 2 ================= --}}
    <section class="pdf-page page-break">

        <div class="evaluation-block">

            <h4>
                Strategi Menu untuk Bulan Depan
            </h4>

            <p>
                {{ $report->next_month_strategy }}
            </p>

        </div>

        <h4 class="weekly-title">
            Pantauan Nutrisi Mingguan Di Bulan Ini
        </h4>

        <div class="weekly-group">

            @foreach ($weeklyCharts as $index => $week)

                <div class="weekly-item">

                    <h5>
                        Minggu {{ $index + 1 }}
                    </h5>

                    <div class="weekly-charts">

                        <img
                            src="{{ $week['energy'] }}"
                            class="mini-chart-img"
                        >

                        <img
                            src="{{ $week['protein'] }}"
                            class="mini-chart-img"
                        >

                        <img
                            src="{{ $week['fat'] }}"
                            class="mini-chart-img"
                        >

                    </div>

                </div>

            @endforeach

        </div>

    </section>

    {{-- ================= PAGE 3 ================= --}}
    <section class="pdf-page page-break">

        <div class="motivation-card">

            <h4>
                Pelukan Hangat untuk Bunda Hebat!
            </h4>

            <p>
                {{ $report->parent_motivation }}
            </p>

        </div>

        <div class="mission-card">

            <img
                src="{{ public_path('images/mission.svg') }}"
                class="mission-img"
            >

            <div class="mission-content">

                <h4>
                    {{ $report->mission_title }}
                </h4>

                <p>
                    {{ $report->mission_content }}
                </p>

            </div>

        </div>

        <div class="reminder-card">

            <img
                src="{{ public_path('images/reminder.svg') }}"
                class="reminder-icon"
            >

            <p>
                Wah, nggak terasa sudah akhir bulan.
                Yuk lihat lagi asupan nutrisi si kecil bulan ini.
                Semua ini berdasarkan scan makanan yang ayah dan ibu lakukan tiap hari,
                jadi jangan lupa rajin scan ya!
            </p>

        </div>

        <div class="footer">
            © 2026 Nutralyse Insight | Sistem Monitoring dan Evaluasi Asupan Nutrisi Balita.
        </div>

    </section>

</body>
</html>