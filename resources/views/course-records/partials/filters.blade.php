@php
    $filtersOpen = collect(['period', 'class', 'lecturer_code', 'range'])->contains(fn ($key) => filled(request()->query($key)));
@endphp

<form method="GET" action="{{ route('course-records.index') }}" data-autosubmit class="space-y-6">
    {{-- Preserve active sort query parameters across filter updates --}}
    @if(request()->filled('sort'))
        <input type="hidden" name="sort" value="{{ request('sort') }}">
    @endif
    @if(request()->filled('direction'))
        <input type="hidden" name="direction" value="{{ request('direction') }}">
    @endif

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
                @foreach ($periodOptions as $option)
                    <option value="{{ $option }}" @selected(request()->query('period') === $option)>{{ $option }}</option>
                @endforeach
            </x-form.select>
        </div>

        <div>
            <x-form.label for="class">Class</x-form.label>
            <x-form.select name="class">
                <option value="">All</option>
                @foreach ($classOptions as $option)
                    <option value="{{ $option }}" @selected(request()->query('class') === $option)>{{ $option }}</option>
                @endforeach
            </x-form.select>
        </div>

        <div>
            <x-form.label for="lecturer_code">Lecturer</x-form.label>
            <x-form.select name="lecturer_code">
                <option value="">All</option>
                @foreach ($lecturerOptions as $option)
                    <option value="{{ $option }}" @selected(request()->query('lecturer_code') === $option)>{{ $option }}</option>
                @endforeach
            </x-form.select>
        </div>

        <div>
            <x-form.label for="range">Range</x-form.label>
            <x-form.select name="range">
                <option value="">All</option>
                <option value="high" @selected(request()->query('range') === 'high')>High (&ge; 20)</option>
                <option value="low" @selected(request()->query('range') === 'low')>Low (&lt; 20)</option>
            </x-form.select>
        </div>
    </div>
</form>