<form method="GET" action="{{ route('statistik.asupan') }}">

    <div class="dropdown-wrapper">

        <img
            src="{{ asset('icons/calendar.svg') }}"
            class="icon-left"
        >

        <select
            name="month_year"
            onchange="this.form.submit()"
        >

            @for ($i = 0; $i < 12; $i++)

                @php
                    $date = now()->subMonths($i);
                    $value = $date->format('Y-m');
                @endphp

                <option
                    value="{{ $value }}"
                    {{ $value == $year.'-'.$month ? 'selected' : '' }}
                >

                    {{ $date->translatedFormat('F Y') }}

                </option>

            @endfor

        </select>

        <img
            src="{{ asset('icons/dropdown.svg') }}"
            class="icon-right"
        >

    </div>

</form>