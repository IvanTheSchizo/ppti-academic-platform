@php
    $normalizeFilter = fn (string $key) => array_values(array_map(
        'strval',
        array_filter((array) request()->query($key, []), fn ($v) => is_scalar($v) && trim((string) $v) !== '')
    ));

    $selectedBatches = $normalizeFilter('batch');
    $selectedStatuses = $normalizeFilter('status');
    $selectedClasses = $normalizeFilter('class');

    $batchOptions = $batches->map(fn ($batch) => ['value' => $batch->batch_name, 'label' => $batch->batch_name])->all();
    $statusOptions = collect(\App\Models\Student::STATUSES)->map(fn ($status) => ['value' => $status, 'label' => $status])->all();
    $classOptions = $classes->map(fn ($classCode) => ['value' => $classCode, 'label' => $classCode])->all();

    $filtersOpen = $selectedBatches !== []
        || $selectedStatuses !== []
        || $selectedClasses !== []
        || collect(['gpa_min', 'gpa_max'])->contains(fn ($key) => filled(request()->query($key)));
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
            <x-form.label>Batch</x-form.label>
            <x-form.multi-select
                name="batch[]"
                :options="$batchOptions"
                :selected="$selectedBatches"
                placeholder="All"
                search-placeholder="Search batches..."
                empty-message="No batches available."
            />
        </div>

        <div>
            <x-form.label>Status</x-form.label>
            <x-form.multi-select
                name="status[]"
                :options="$statusOptions"
                :selected="$selectedStatuses"
                placeholder="All"
                :searchable="false"
            />
        </div>

        <div>
            <x-form.label>Class</x-form.label>
            <x-form.multi-select
                name="class[]"
                :options="$classOptions"
                :selected="$selectedClasses"
                placeholder="All"
                search-placeholder="Search classes..."
                empty-message="No classes available."
            />
        </div>

        <div>
            <x-form.label>GPA Range</x-form.label>
            <x-form.range-input name="gpa" :min="request()->query('gpa_min')" :max="request()->query('gpa_max')" />
        </div>
    </div>
</form>