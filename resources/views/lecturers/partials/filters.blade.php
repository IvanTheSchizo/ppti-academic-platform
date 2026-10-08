@php
    $filtersOpen = collect(['status'])->contains(fn ($key) => filled(request()->query($key)));
@endphp

<form method="GET" action="{{ route('lecturers.index') }}" data-autosubmit class="space-y-6">
    <div class="flex gap-4">
        <x-form.search-input name="q" :value="request()->query('q')" aria-label="Search lecturers" class="flex-1" />

        <x-ui.button type="button" variant="secondary" data-toggle="#lecturer-filters" aria-controls="lecturer-filters" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}">
            <x-ui.icon name="filter_alt" /> Filter
        </x-ui.button>
    </div>

    <div id="lecturer-filters" @unless ($filtersOpen) hidden @endunless class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <x-form.label for="status">Status</x-form.label>
            <x-form.select name="status">
                <option value="">All</option>
                @foreach (['Active', 'Inactive'] as $option)
                    <option value="{{ $option }}" @selected(request()->query('status') === $option)>{{ $option }}</option>
                @endforeach
            </x-form.select>
        </div>
    </div>
</form>