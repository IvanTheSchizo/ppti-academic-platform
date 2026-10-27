{{--
    Students box shared by the Course Record and Grades forms.
    Rows are built by app.js: search results are added with a click, and a student can be removed with the delete icon.
    $withGrade     adds a Grade column (letter grade select per student).
    $initialStudents rows to show on load (after a validation error).
    $classSelect   CSS selector of a class select; when set, shows an "Add all from class" button.
    $courseSelect  CSS selector of a course record select; when set, picking one loads its students.
--}}
@php
    $withGrade = $withGrade ?? false;
    $classSelect = $classSelect ?? null;
    $courseSelect = $courseSelect ?? null;
    $columns = $withGrade ? 6 : 5;
@endphp

<div data-student-picker
     data-search-url="{{ route('input-data.students.search') }}"
     data-record-url="{{ route('input-data.record-students', '__ID__') }}"
     data-initial='@json($initialStudents ?? [])'
     @if ($withGrade) data-with-grade data-grade-options='@json(array_keys(\App\Models\StudentGrade::GRADE_POINTS))' @endif
     @if ($classSelect) data-class-select="{{ $classSelect }}" @endif
     @if ($courseSelect) data-course-select="{{ $courseSelect }}" @endif
     class="rounded-ui border border-border p-4">
    <div class="mb-4 flex items-center justify-between gap-4">
        <h3 class="font-semibold text-text">Students</h3>

        @if ($classSelect)
            <x-ui.button type="button" variant="secondary" size="sm" data-picker-add-class>
                <x-ui.icon name="groups" /> Add all from class
            </x-ui.button>
        @endif
    </div>

    <div class="relative">
        <x-form.search-input type="search" placeholder="Search student by name or NIM to add" aria-label="Search student by name or NIM to add" autocomplete="off" data-picker-search :filled="true" />

        <ul data-picker-results hidden class="absolute inset-x-0 top-full z-10 mt-1 max-h-64 overflow-y-auto rounded-ui border border-border bg-surface py-1 text-body"></ul>
    </div>

<div class="mt-4 overflow-x-auto">
        <table class="w-full border-collapse text-left text-body">
            <thead>
                <tr class="border-b border-border text-caption uppercase tracking-wide text-muted bg-surface">
                    <th class="w-10 px-2 py-2 font-normal">
                        <input type="checkbox" data-picker-select-all checked class="rounded border-border text-primary focus:ring-primary">
                    </th>
                    <th class="px-2 py-2 font-normal">NIM</th>
                    <th class="px-2 py-2 font-normal">Name</th>
                    <th class="px-2 py-2 font-normal">Batch</th>
                    <th class="px-2 py-2 font-normal">Class</th>
                    @if ($withGrade)
                        <th class="px-2 py-2 font-normal">Grade</th>
                    @endif
                </tr>
            </thead>
            <tbody data-picker-body class="[&>tr]:border-b [&>tr]:border-border [&>tr]:transition-colors hover:[&>tr]:bg-page"></tbody>
        </table>

        <p data-picker-empty class="px-2 py-6 text-center text-muted">No students added yet.</p>
    </div>
</div>
</div>
