<x-app-layout>

    <div class="p-6 space-y-6">

        @php
            $child = $children[0] ?? null;
        @endphp

        @if($child)

        <!-- HEADER -->
        <div class="header">
            <div class="profile-img">
                <img src="{{ $child['photo_url'] ?? 'https://via.placeholder.com/120' }}" 
                     alt="Profile">
            </div>
        </div>

        <div class="photo-space"></div> <!-- 🔥 TAMBAHAN -->


        <!-- NAME -->
        <h2 class="name">{{ $child['name'] ?? '-' }}</h2>

        <!-- GRID -->
        <div class="grid">

            <div class="card">
                <img src="{{ asset('icons/weight.svg') }}" class="card-icon">
                <p>Berat Badan</p>
                <h3>{{ $child['weight'] ?? '-' }} Kg</h3>
            </div>

            <div class="card highlight">
                <img src="{{ asset('icons/height.svg') }}" class="card-icon">
                <p>Tinggi Badan</p>
                <h3>{{ $child['height'] ?? '-' }} Cm</h3>
            </div>

            <div class="card">
                <img src="{{ asset('icons/age.svg') }}" class="card-icon">
                <p>Umur Ananda</p>
                <h3>{{ $child['age'] ?? '-' }} Tahun</h3>
            </div>

            <div class="card">
                <img src="{{ asset('icons/gender.svg') }}" class="card-icon">
                <p>Jenis Kelamin</p>
                <h3>{{ $child['gender'] ?? '-' }}</h3>
            </div>

            <div class="card highlight">
                <img src="{{ asset('icons/alergy.svg') }}" class="card-icon">
                <p>Alergi</p>
                <h3>{{ $child['allergy'] ?? '-' }}</h3>
            </div>

            <div class="card">
                <img src="{{ asset('icons/parent.svg') }}" class="card-icon">
                <p>Ayah & Bunda</p>
                <h3>Nirmala</h3>
            </div>

        </div>

        @endif

    </div>

</x-app-layout>