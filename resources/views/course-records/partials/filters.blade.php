@php
    $normalizeFilter = fn (string $key) => array_values(array_map(
        'strval',
        array_filter((array) request()->query($key, []), fn ($v) => is_scalar($v) && trim((string) $v) !== '')
    ));

    $selectedPeriods = $normalizeFilter('period');
    $selectedClasses = $normalizeFilter('class');
    $selectedLecturers = $normalizeFilter('lecturer_code');
    $selectedCourses = $normalizeFilter('course');

    $periodSelectOptions = $periodOptions->map(fn ($option) => ['value' => $option, 'label' => $option])->all();
    $classSelectOptions = $classOptions->map(fn ($option) => ['value' => $option, 'label' => $option])->all();
    $lecturerSelectOptions = $lecturerOptions->map(fn ($option) => ['value' => $option, 'label' => $option])->all();
    $courseSelectOptions = $courseOptions->map(fn ($option) => [
        'value' => $option->course_code,
        'label' => "{$option->course_code} — {$option->course_name}",
        'pillLabel' => $option->course_code,
    ])->all();

    $filtersOpen = $selectedPeriods !== []
        || $selectedClasses !== []
        || $selectedLecturers !== []
        || $selectedCourses !== [];
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
            <x-form.label>Period</x-form.label>
            <x-form.multi-select
                name="period[]"
                :options="$periodSelectOptions"
                :selected="$selectedPeriods"
                placeholder="All"
                search-placeholder="Search periods..."
                empty-message="No periods available."
            />
        </div>

        <div>
            <x-form.label>Class</x-form.label>
            <x-form.multi-select
                name="class[]"
                :options="$classSelectOptions"
                :selected="$selectedClasses"
                placeholder="All"
                search-placeholder="Search classes..."
                empty-message="No classes available."
            />
        </div>

        <div>
            <x-form.label>Lecturer</x-form.label>
            <x-form.multi-select
                name="lecturer_code[]"
                :options="$lecturerSelectOptions"
                :selected="$selectedLecturers"
                placeholder="All"
                search-placeholder="Search lecturers..."
                empty-message="No lecturers available."
            />
        </div>

        <div>
            <x-form.label>Course</x-form.label>
            <x-form.multi-select
                name="course[]"
                :options="$courseSelectOptions"
                :selected="$selectedCourses"
                placeholder="All"
                search-placeholder="Search courses..."
                empty-message="No courses available."
            />
        </div>
    </div>
</form>