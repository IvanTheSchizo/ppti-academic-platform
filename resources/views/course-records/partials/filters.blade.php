@php
    $filtersOpen = collect(['period', 'class', 'lecturer_code', 'range'])->contains(fn ($key) => filled(request()->query($key)));
@endphp

<form method="GET" action="{{ route('course-records.index') }}" data-autosubmit class="space-y-6">
    <div class="flex gap-4">
        <x-form.search-input name="q" :value="request()->query('q')" aria-label="Search course records" class="flex-1" />

        <x-ui.button type="button" variant="secondary" data-toggle="#course-record-filters" aria-controls="course-record-filters" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}">
            <x-ui.icon name="filter_alt" /> Filter
        </x-ui.button>
    </div>

    <div id="course-record-filters" @unless ($filtersOpen) hidden @endunless class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <x-form.label for="period">Period</x-form.label>
            <x-form.select name="period">
                <option value="">All</option>
                @foreach (['2025 - Odd', '2024 - Even', '2024 - Odd'] as $option)
                    <option value="{{ $option }}" @selected(request()->query('period') === $option)>{{ $option }}</option>
                @endforeach
            </x-form.select>
        </div>

        <div>
            <x-form.label for="class">Class</x-form.label>
            <x-form.select name="class">
                <option value="">All</option>
                @foreach (['L4BC', 'L4CC', 'L4B1', 'L2AC'] as $option)
                    <option value="{{ $option }}" @selected(request()->query('class') === $option)>{{ $option }}</option>
                @endforeach
            </x-form.select>
        </div>

        <div>
            <x-form.label for="lecturer_code">Lecturer</x-form.label>
            <x-form.select name="lecturer_code">
                <option value="">All</option>
                @foreach (['D010101', 'D0767', 'AAP'] as $option)
                    <option value="{{ $option }}" @selected(request()->query('lecturer_code') === $option)>{{ $option }}</option>
                @endforeach
            </x-form.select>
        </div>

        <div>
            <x-form.label>Range</x-form.label>
            <x-form.select name="range">
                <option value="">All</option>
                <option value="high">High</option>
                <option value="low">Low</option>
            </x-form.select>
        </div>
    </div>
</form>