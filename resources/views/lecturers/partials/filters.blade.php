@php
    $filtersOpen = collect(['status', 'jja', 'latest_education'])->contains(fn ($key) => filled(request()->query($key)));
@endphp

<form method="GET" action="{{ route('lecturers.index') }}" data-autosubmit class="space-y-6">
    {{-- Preserve active sort query parameters across filter submissions --}}
    @if(request()->filled('sort'))
        <input type="hidden" name="sort" value="{{ request('sort') }}">
    @endif
    @if(request()->filled('direction'))
        <input type="hidden" name="direction" value="{{ request('direction') }}">
    @endif

    <div class="flex gap-4">
        <x-form.search-input name="q" :value="request()->query('q')" aria-label="Search lecturers" class="flex-1" />

        <x-ui.button type="button" variant="secondary" data-toggle="#lecturer-filters" aria-controls="lecturer-filters" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}">
            <x-ui.icon name="filter_alt" /> Filter
        </x-ui.button>
    </div>

    <div id="lecturer-filters" @unless ($filtersOpen) hidden @endunless class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <x-form.label for="status">Status</x-form.label>
            <x-form.select name="status">
                <option value="">All</option>
                @foreach (['Active', 'Inactive', 'On Leave'] as $option)
                    <option value="{{ $option }}" @selected(request()->query('status') === $option)>{{ $option }}</option>
                @endforeach
            </x-form.select>
        </div>

        <div>
            <x-form.label for="jja">Rank (JJA)</x-form.label>
            <x-form.select name="jja">
                <option value="">All</option>
                @foreach ($jjaOptions as $option)
                    <option value="{{ $option }}" @selected(request()->query('jja') === $option)>{{ $option }}</option>
                @endforeach
            </x-form.select>
        </div>

        <div>
            <x-form.label for="latest_education">Latest Education</x-form.label>
            <x-form.select name="latest_education">
                <option value="">All</option>
                @foreach ($educationOptions as $option)
                    <option value="{{ $option }}" @selected(request()->query('latest_education') === $option)>{{ $option }}</option>
                @endforeach
            </x-form.select>
        </div>
    </div>
</form>