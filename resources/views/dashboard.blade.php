<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    {{-- TOPBAR --}}
    <div class="dashboard-topbar">

        <div class="dropdown-group">

            <form
                method="POST"
                action="{{ route('dashboard.set-child') }}">

                @csrf

                <div class="dropdown-wrapper">

                    <select
                        name="child_id"
                        onchange="this.form.submit()"
                        class="child-dropdown"
                    >

                        <option disabled hidden>

                            Pilih Ananda

                        </option>

                        @foreach($children as $child)

                            <option
                                value="{{ $child->id }}"
                                {{ $activeChildId == $child->id ? 'selected' : '' }}
                            >

                                {{ $child->name }}

                            </option>

                        @endforeach

                    </select>

                    <img
                        src="{{ asset('icons/dropdown.svg') }}"
                        class="dropdown-icon-right"
                    >

                </div>

            </form>
        </div>

        <div class="hello-text">

            Hallo, {{ $activeChild->name }}

        </div>

    </div>

        <div class="dashboard-container">

            {{-- ================= ANMI ================= --}}
            <div class="card-ANMI">
                <h3>Akumulasi Nutrisi Minggu Ini</h3>

                <div class="nutrition-wrapper">
                    <div class="nutrition-item">
                        <p>Energi</p>
                        <canvas id="energyChart"></canvas>
                    </div>

                    <div class="nutrition-item">
                        <p>Protein</p>
                        <canvas id="proteinChart"></canvas>
                    </div>

                    <div class="nutrition-item">
                        <p>Lemak</p>
                        <canvas id="fatChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- ================= LMHI ================= --}}
            <div class="card-LMHI">

                <h3 class="lmhi-title">Log Makanan Hari Ini</h3>

                @if(empty($lmhiLogs) || $lmhiLogs->isEmpty())

                    <x-empty-state 
                        image="/images/empty-data.png"
                        title="Belum Ada Log Makanan Untuk Hari Ini"
                        description="Scan makanan untuk mulai tracking nutrisi."
                    />

                @else

                    <div class="lmhi-layout">

                        <div class="lmhi-chart">
                            <canvas id="lmhiPieChart"
                                data-energy="{{ $lmhiLogs->sum('energy') ?? 0 }}"
                                data-protein="{{ $lmhiLogs->sum('protein') ?? 0 }}"
                                data-fat="{{ $lmhiLogs->sum('fat') ?? 0 }}">
                            </canvas>

                            <div class="lmhi-legend">
                                <span class="legend energy">Energi</span>
                                <span class="legend protein">Protein</span>
                                <span class="legend fat">Lemak</span>
                            </div>
                        </div>

                        <div class="lmhi-list">
                            @foreach ($lmhiLogs as $log)
                                <div class="lmhi-item">
                                    {{ $log->menu_makanan ?? '-' }}
                                    (Energi {{ $log->energy ?? 0 }} Kkal,
                                    Protein {{ $log->protein ?? 0 }} g,
                                    Lemak {{ $log->fat ?? 0 }} g)
                                </div>
                            @endforeach
                        </div>

                    </div>

                @endif

            </div>

            {{-- ================= SNB ================= --}}
            <div class="card-SNB">
                <h3 class="SNB-title">Sinyal Nutrisi Balita</h3>

                <div class="SNB-content">
                    <img src="/images/SNB-logo.png" class="SNB-icon" alt="icon">

                    <p class="SNB-text">
                        {{ $earlyWarning ?? 'Belum ada evaluasi AI.' }}
                    </p>

                </div>
            </div>

            {{-- ================= WEEKLY CHART ================= --}}
            <div class="card-weekly-chart">

                <div class="nutrition-filter">
                    <button class="filter-btn active" data-type="energy">Energi</button>
                    <button class="filter-btn" data-type="protein">Protein</button>
                    <button class="filter-btn" data-type="fat">Lemak</button>
                </div>

                <div class="weekly-chart">
                    <canvas id="weeklyChart"></canvas>
                </div>

            </div>

            {{-- ================= TSKH ================= --}}
            <div class="card-TSKH">

                <h3 class="TSKH-title">Tabel Status Kecukupan Harian</h3>

                <div class="TSKH-table">

                    <div class="TSKH-header">
                        <span>Jenis Nutrisi</span>
                        <span>Terpenuhi</span>
                        <span>Target</span>
                        <span>Sisa</span>
                    </div>

                    <div class="TSKH-row">
                        <span>Energi</span>
                        <span>{{ $energy ?? 0 }} kkal</span>
                        <span>{{ $targetEnergy ?? 0 }} kkal</span>
                        <span>{{ $sisaEnergy ?? 0 }} kkal</span>
                    </div>

                    <div class="TSKH-row">
                        <span>Protein</span>
                        <span>{{ $protein ?? 0 }} g</span>
                        <span>{{ $targetProtein ?? 0 }} g</span>
                        <span>{{ $sisaProtein ?? 0 }} g</span>
                    </div>

                    <div class="TSKH-row">
                        <span>Lemak</span>
                        <span>{{ $fat ?? 0 }} g</span>
                        <span>{{ $targetFat ?? 0 }} g</span>
                        <span>{{ $sisaFat ?? 0 }} g</span>
                    </div>

                </div>

                <div class="TSKH-footer">
                    <img src="{{ asset('images/note.png') }}" alt="note" class="note-icon">

                    <div class="footer-text">
                        <p>Butuh ide masakan?</p>
                        <p>Cek rekomendasi menu untuk memenuhi kebutuhan nutrisi harian.</p>
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- ================= JS ================= --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>


    <script>
        const weeklyData = @json($weeklyData ?? []);
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            createDonutChart(
                "energyChart",
                {{ $weeklyEnergy ?? 0 }},
                {{ $weeklyTargetEnergy ?? 1 }},
                "#FFD83D",
                "Kcal"
            );

            createDonutChart(
                "proteinChart",
                {{ $weeklyProtein ?? 0 }},
                {{ $weeklyTargetProtein ?? 1 }},
                "#9FB608",
                "g"
            );

            createDonutChart(
                "fatChart",
                {{ $weeklyFat ?? 0 }},
                {{ $weeklyTargetFat ?? 1 }},
                "#AA2B1D",
                "g"
            );

            initLMHIChart();

        });
        </script>
    
</x-app-layout>