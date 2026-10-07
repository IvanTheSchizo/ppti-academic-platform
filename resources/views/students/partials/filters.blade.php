@php
    $filtersOpen = collect(['batch', 'status', 'class', 'gpa_min', 'gpa_max'])->contains(fn ($key) => filled(request()->query($key)));
@endphp

<form method="GET" action="{{ route('students.index') }}" data-autosubmit class="space-y-6">
    <div class="flex gap-4">
        <x-form.search-input name="q" :value="request()->query('q')" aria-label="Search students" class="flex-1" />

        <x-ui.button type="button" variant="secondary" data-toggle="#student-filters" aria-controls="student-filters" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}">
            <x-ui.icon name="filter_alt" /> Filter
        </x-ui.button>
    </div>

    <div id="student-filters" @unless ($filtersOpen) hidden @endunless class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <x-form.label for="batch">Batch</x-form.label>
            <x-form.select name="batch">
                <option value="">All</option>
                @foreach ($batches as $option)
                    <option value="{{ $option->batch_name }}" @selected(request()->query('batch') === $option->batch_name)>
                        {{ $option->batch_name }}
                    </option>
                @endforeach
            </x-form.select>
        </div>

        <div>
            <x-form.label for="status">Status</x-form.label>
            <x-form.select name="status">
                <option value="">All</option>
                @foreach (['Active', 'Graduated', 'On Leave'] as $option)
                    <option value="{{ $option }}" @selected(request()->query('status') === $option)>{{ $option }}</option>
                @endforeach
            </x-form.select>
        </div>

        <div>
            <x-form.label for="class">Class</x-form.label>
            <x-form.select name="class">
                <option value="">All</option>
                @foreach ($classes as $option)
                    <option value="{{ $option }}" @selected(request()->query('class') === $option)>{{ $option }}</option>
                @endforeach
            </x-form.select>
        </div>

        <div>
            <x-form.label>GPA Range</x-form.label>
            <x-form.range-input name="gpa" :min="request()->query('gpa_min')" :max="request()->query('gpa_max')" />
        </div>
    </div>
</form>