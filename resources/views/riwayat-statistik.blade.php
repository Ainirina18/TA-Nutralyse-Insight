<x-app-layout>

    <link
        rel="stylesheet"
        href="{{ asset('css/riwayat-statistik.css') }}"
    >

    <div class="history-wrapper">

        {{-- HEADER --}}
        <div class="history-header">

            <div class="history-title-area">

                <h1>
                    Riwayat Statistik Nutrisi
                </h1>

                <p>
                    Pantau laporan evaluasi nutrisi yang pernah dibuat
                </p>

            </div>

        </div>

        {{-- GRID --}}
        <div class="history-grid">

            @forelse($reports as $report)

                <div class="history-card">

                    {{-- TOP --}}
                    <div class="card-top">

                        <div class="month-icon">

                            <img
                                src="{{ asset('icons/calendar.svg') }}"
                            >

                        </div>

                        <div class="month-info">

                            <h3>

                                {{ \Carbon\Carbon::create()
                                    ->month($report->report_month)
                                    ->translatedFormat('F') }}

                                {{ $report->report_year }}

                            </h3>

                            <span class="status-badge available">

                                Selesai

                            </span>

                        </div>

                    </div>

                    {{-- NUTRITION --}}
                    <div class="nutrition-summary">

                        <div class="nutrition-chip energy">

                            Energi
                            {{ number_format($report->total_energy) }}

                        </div>

                        <div class="nutrition-chip protein">

                            Protein
                            {{ number_format($report->total_protein) }}

                        </div>

                        <div class="nutrition-chip fat">

                            Lemak
                            {{ number_format($report->total_fat) }}

                        </div>

                    </div>

                    {{-- DATE --}}
                    <p class="generated-date">

                        Dibuat pada
                        {{ \Carbon\Carbon::parse($report->generated_at)
                            ->translatedFormat('d F Y') }}

                    </p>

                    {{-- BUTTON --}}
                    <a
                        href="{{ route('statistik.asupan', [
                            'month_year' =>
                            $report->report_year . '-' .
                            str_pad($report->report_month, 2, '0', STR_PAD_LEFT)
                        ]) }}"
                        class="view-btn"
                    >

                        Lihat Evaluasi →

                    </a>

                </div>

            @empty

                <div class="empty-history">

                    <img
                        src="{{ asset('images/statistik/empty-report.png') }}"
                        class="empty-img"
                    >

                    <h3>
                        Belum Ada Riwayat Statistik
                    </h3>

                    <p>
                        Laporan evaluasi nutrisi yang telah dibuat
                        akan muncul di halaman ini.
                    </p>

                </div>

            @endforelse

        </div>

    </div>

</x-app-layout>