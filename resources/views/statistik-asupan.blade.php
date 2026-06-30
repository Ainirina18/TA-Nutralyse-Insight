<x-app-layout>

    @if(($pageState ?? null) === 'pending')

            @include('components.statistik-asupan.pending-statistik')

        @elseif(($pageState ?? null) === 'empty')

            @include('components.statistik-asupan.empty-statistik')

        @else
        
       <x-mobile-topbar>

    <div class="topbar-left">

        <form method="GET" action="{{ route('statistik.asupan') }}">

            @include('components.widgets.month-dropdown')

        </form>

    </div>

    <div class="topbar-right">

        <form
            action="{{ route('statistik.export.pdf') }}"
            method="POST"
            id="pdfForm">

            @csrf

            <input
                type="hidden"
                name="month_year"
                value="{{ $year.'-'.$month }}">

            <input
                type="hidden"
                name="energy_chart"
                id="energy_chart">

            <input
                type="hidden"
                name="protein_chart"
                id="protein_chart">

            <input
                type="hidden"
                name="fat_chart"
                id="fat_chart">

            <input
                type="hidden"
                name="weekly_charts"
                id="weekly_charts">

            <input
                type="hidden"
                name="daily_charts"
                id="daily_charts">

            <button
                type="submit"
                class="btn-export">
                Export PDF
            </button>

        </form>

    </div>

</x-mobile-topbar>

            {{-- 🔥 HERO / HEADER UTAMA --}}
            <div class="hero-section">
                <div class="hero-left">
                    <img src="/images/STATISTIK-BULANAN.svg" class="hero-img">
                </div>

                <div class="hero-right">
                    <h1>Jurnal Hebat Si Kecil:</h1>
                    <h2>Edisi {{ \Carbon\Carbon::create()->month((int)$month)->translatedFormat('F') }}</h2>

                <p class="akg-text">
                        Target AKG per hari :
                        Energi {{ $targetEnergy }} kkal |
                        Protein {{ $targetProtein }} g |
                        Lemak {{ $targetFat }} g
                    </p>
                </div>
            </div>

            <div class="layout">

                {{-- LEFT --}}
                <div class="left">

                <div class="card-ANH">

                        <div class="nutrition-filter">
                            <button class="filter-btn active" data-type="energy">Energi</button>
                            <button class="filter-btn" data-type="protein">Protein</button>
                            <button class="filter-btn" data-type="fat">Lemak</button>
                        </div>

                        <div class="daily-chart-card">
                            <canvas id="dailyChart"></canvas>
                        </div>

                    </div>

                    <div class="week-filter-wrapper">

                        <div class="week-dropdown">

                            <img src="{{ asset('icons/calendar.svg') }}" class="icon-left">

                            <select id="weekFilter">

                                <option value="week1">Minggu 1</option>
                                <option value="week2">Minggu 2</option>
                                <option value="week3">Minggu 3</option>
                                <option value="week4">Minggu 4</option>
                                <option value="week5">Minggu 5</option>

                            </select>

                            <img src="{{ asset('icons/dropdown.svg') }}" class="icon-right">

                        </div>

                    </div>

                    <div class="card-WNH">

                        <div class="weekly-chart-group">

                            <div class="weekly-chart-card">
                                <canvas id="energyChart"></canvas>
                            </div>

                            <div class="weekly-chart-card">
                                <canvas id="proteinChart"></canvas>
                            </div>

                            <div class="weekly-chart-card">
                                <canvas id="fatChart"></canvas>
                            </div>

                        </div>

                        <div class="chart-dots">
                            <span class="dot active"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                        </div>

                    </div>

                </div>

                {{-- RIGHT --}}
                <div class="right">

                    <div class="card-reminder">

                        <div class="reminder-header">
                            Reminder
                        </div>

                        <div class="reminder-body">

                            <img src="{{ asset('images/reminder.svg') }}" alt="Reminder">

                            <p>
                                Wah, nggak terasa sudah akhir bulan.
                                Yuk lihat lagi asupan nutrisi si kecil bulan ini.
                                Semua ini berdasarkan scan makanan yang ayah dan ibu lakukan tiap hari,
                                jadi jangan lupa rajin scan ya !
                            </p>

                        </div>

                    </div>

                    <div class="evaluation-card">

                        <div class="evaluation-section">
                            <h3>Bagaimana Perjalanan Nutrisi Bulan Ini?</h3>

                            <p>
                                {{ $nutritionJourney }}
                            </p>
                        </div>

                        <div class="evaluation-section">
                            <h3>Evaluasi Celah Nutrisi Bulanan</h3>

                            <p>
                                {{ $nutritionGap }}
                            </p>
                        </div>

                        <div class="evaluation-section">
                            <h3>Strategi Menu untuk Bulan Depan</h3>

                            <p>
                                {{ $nextMonthStrategy }}
                            </p>
                        </div>

                        <div class="evaluation-section">
                            <h3>Pelukan Hangat untuk Bunda Hebat!</h3>

                            <p>
                                {{ $parentMotivation }}
                            </p>
                        </div>

                    </div>

                </div>

            </div>

            <div class="mission-wrapper">

                    <div class="mission-card">

                        <img
                            src="{{ asset('images/mission.svg') }}"
                            alt="Mission"
                            class="mission-img"
                        >

                        <div class="mission-content">

                            <h3>
                                {{ $missionTitle }}
                            </h3>

                            <p>
                                {{ $missionContent }}
                            </p>

                        </div>

                    </div>

                </div>

        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
        <script src="{{ asset('js/statistik-asupan.js') }}"></script>

        <script>
            const dailyData = @json($dailyChart ?? []);
            const weeklyData = @json($weeklyChart ?? []);
        </script>

        <script>

            document.getElementById('pdfForm')
            .addEventListener('submit', async function (e) {

                e.preventDefault();

                // 🔥 DAILY LINE CHARTS
                const dailyCharts = await generateDailyChartsForPdf();

                document.getElementById('daily_charts').value =
                    JSON.stringify(dailyCharts);

                // 🔥 WEEKLY BAR CHARTS
                const weeklyCharts = await generateWeeklyChartsForPdf();

                document.getElementById('weekly_charts').value =
                    JSON.stringify(weeklyCharts);

                this.submit();
            });

        </script>

        <script>
            document.addEventListener("DOMContentLoaded", function () {

                const container = document.querySelector('.weekly-chart-group');
                const cards = document.querySelectorAll('.weekly-chart-card');

                if (!container || cards.length === 0) return;

                const observer = new IntersectionObserver((entries) => {

                    entries.forEach(entry => {

                        if (entry.isIntersecting) {

                            cards.forEach(card => card.classList.remove('active-card'));

                            entry.target.classList.add('active-card');
                        }
                    });

                }, {
                    root: container,
                    threshold: 0.6 // makin besar = makin ketat fokusnya
                });

                cards.forEach(card => observer.observe(card));
            });
        </script>

    @endif

</x-app-layout>