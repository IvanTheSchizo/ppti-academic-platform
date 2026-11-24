@php
    $normalizeFilter = fn (string $key) => array_values(array_map(
        'strval',
        array_filter((array) request()->query($key, []), fn ($v) => is_scalar($v) && trim((string) $v) !== '')
    ));

    $selectedStatuses = $normalizeFilter('status');
    $selectedBatches = $normalizeFilter('batch');
    $selectedJjas = $normalizeFilter('jja');
    $selectedEducations = $normalizeFilter('latest_education');

    $statusOptions = collect(['Active', 'Inactive'])->map(fn ($status) => ['value' => $status, 'label' => $status])->all();
    $batchOptions = $batches->map(fn ($batch) => ['value' => $batch->batch_name, 'label' => $batch->batch_name])->all();
    $jjaSelectOptions = $jjaOptions->map(fn ($option) => ['value' => $option, 'label' => $option])->all();
    $educationSelectOptions = $educationOptions->map(fn ($option) => ['value' => $option, 'label' => $option])->all();

    $filtersOpen = $selectedStatuses !== []
        || $selectedBatches !== []
        || $selectedJjas !== []
        || $selectedEducations !== [];
@endphp

<form method="GET" action="{{ route('lecturers.index') }}" data-autosubmit class="space-y-4">
    {{-- Preserve active sort query parameters across filter submissions --}}
    @if(request()->filled('sort'))
        <input type="hidden" name="sort" value="{{ request('sort') }}">
    @endif
    @if(request()->filled('direction'))
        <input type="hidden" name="direction" value="{{ request('direction') }}">
    @endif

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <x-form.search-input name="q" :value="request()->query('q')" aria-label="Search lecturers" class="min-w-0 flex-1" />

        <div class="flex shrink-0 items-center justify-end gap-2">
            <x-ui.button type="button" variant="secondary" data-toggle="#lecturer-filters" aria-controls="lecturer-filters" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}">
                <x-ui.icon name="filter_alt" /> Filter
            </x-ui.button>
        </div>
    </div>

    <div id="lecturer-filters" @unless ($filtersOpen) hidden @endunless class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
            <x-form.label>Rank (JJA)</x-form.label>
            <x-form.multi-select
                name="jja[]"
                :options="$jjaSelectOptions"
                :selected="$selectedJjas"
                placeholder="All"
                search-placeholder="Search ranks..."
                empty-message="No ranks available."
            />
        </div>

        <div>
            <x-form.label>Latest Education</x-form.label>
            <x-form.multi-select
                name="latest_education[]"
                :options="$educationSelectOptions"
                :selected="$selectedEducations"
                placeholder="All"
                search-placeholder="Search education..."
                empty-message="No education levels available."
            />
        </div>
    </div>
</form>
