@extends('layouts.app')

@section('title', 'Course Record')

@section('content')
    <div class="space-y-card">
        <x-ui.breadcrumb :items="[
            ['label' => 'Course Records', 'href' => route('course-records.index')],
            ['label' => $record->record_code],
        ]" />

        <x-ui.page-header :title="$record->record_code" :subtitle="$record->course?->course_name">
            <x-slot:actions>
                <x-ui.button variant="secondary" type="button" data-modal-open="#edit-record-modal">
                    <x-ui.icon name="pencil" /> Edit
                </x-ui.button>
                <x-ui.button type="button" variant="accent" data-modal-open="#download-record-students-modal">
                    <x-ui.icon name="download" /> Download
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        @if ($errors->any())
            <x-ui.alert variant="danger">
                <p class="font-semibold">Please correct the following:</p>
                <ul class="mt-2 list-disc pl-5">
                    @foreach (array_unique($errors->all()) as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <x-ui.card title="Course Info" icon="school">
            <x-ui.detail-list>
                <x-ui.detail label="Course Code">{{ $record->course?->course_code ?? '-' }}</x-ui.detail>
                <x-ui.detail label="Course Name">{{ $record->course?->course_name ?? '-' }}</x-ui.detail>
                <x-ui.detail label="Academic Period">{{ $record->period?->period_name ?? '-' }}</x-ui.detail>
                <x-ui.detail label="Class">{{ $record->classGroup?->class_code ?? '-' }}</x-ui.detail>
                <x-ui.detail label="Lecturer">
                    @if ($record->lecturer)
                        <a href="{{ route('lecturers.profile', $record->lecturer->id) }}" class="rounded-ui hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">{{ $record->lecturer->name }}</a>
                    @else
                        -
                    @endif
                </x-ui.detail>
                <x-ui.detail label="IKADQ">{{ $record->lecturerPerformance ? number_format((float) $record->lecturerPerformance->ikadq, 2) : '-' }}</x-ui.detail>
            </x-ui.detail-list>
        </x-ui.card>

        {{-- Students: the search enrolls a student on click ("Add Students" jumps to it); "Edit" shows a remove icon per row. --}}
        <x-ui.card>
            <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
                <h2 class="text-section font-semibold text-text">Students</h2>

                <div class="flex items-center gap-3">
                    <x-ui.button type="button" variant="secondary" data-modal-open="#add-student-modal">
                        <x-ui.icon name="plus" /> Add Students
                    </x-ui.button>
                    <x-ui.button type="button" variant="secondary" class="text-danger hover:bg-danger/10 hover:text-danger-hover" data-modal-open="#remove-student-modal">
                        <x-ui.icon name="delete" /> Remove Students
                    </x-ui.button>
                </div>
            </div>

            <x-ui.table :headings="[
                ['label' => 'NIM', 'sortable' => false],
                ['label' => 'Name', 'sortable' => false],
                ['label' => 'Batch', 'sortable' => false],
                ['label' => 'Class', 'sortable' => false],
            ]" id="record-students-table">
                @forelse ($students as $student)
                    <tr data-row-href="{{ route('students.profile', $student->id) }}" class="cursor-pointer">
                        <td>{{ $student->nim }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->classGroup?->batch?->batch_name ?? '-' }}</td>
                        <td>{{ $student->classGroup?->class_code ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <x-ui.empty-state title="No students yet" description="Use Add Students to enrol students in this course record." icon="search" />
                        </td>
                    </tr>
                @endforelse

                <x-slot:footer>
                    <x-ui.pagination :paginator="$students" />
                </x-slot:footer>
            </x-ui.table>
        </x-ui.card>
    </div>

    <form id="remove-student-form" method="POST" action="" hidden>
        @csrf
        @method('DELETE')
    </form>

    <x-ui.confirm id="remove-student-confirm" title="Remove student?" message="This removes the student from the course record and deletes their grade for it." confirm-label="Remove" form="remove-student-form" variant="danger" />

    @include('course-records.partials.edit-modal')
    @include('course-records.partials.add-student-modal')
    @include('course-records.partials.remove-student-modal')

    <x-ui.download-modal
        id="download-record-students-modal"
        title="Download Enrolled Students"
        :action="route('course-records.show.export', $record->id)"
        :columns="['NIM', 'Name', 'Batch', 'Class']"
        search-placeholder="Search enrolled students by name or NIM"
        item-label="student"
    >
        @php
            $enrolledStudents = $allStudents->whereIn('id', $enrolledStudentIds);
        @endphp
        @forelse ($enrolledStudents as $student)
            @php
                $batchName = $student->classGroup?->batch?->batch_name ?? '-';
                $classCode = $student->classGroup?->class_code ?? '-';
                $searchString = strtolower($student->nim . ' ' . $student->name . ' ' . $batchName . ' ' . $classCode);
            @endphp
            <tr data-download-row data-search="{{ $searchString }}" class="cursor-pointer transition-colors hover:bg-page">
                <td class="px-3 py-3 text-center">
                    <input type="checkbox" name="ids[]" value="{{ $student->id }}" data-download-cb class="size-4 shrink-0 cursor-pointer rounded border-border text-primary accent-navy focus:ring-primary">
                </td>
                <td class="px-3 py-3 font-medium text-text">{{ $student->nim }}</td>
                <td class="px-3 py-3">{{ $student->name }}</td>
                <td class="px-3 py-3">{{ $batchName }}</td>
                <td class="px-3 py-3 font-medium text-text">{{ $classCode }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="p-6 text-center text-muted">No students enrolled yet.</td>
            </tr>
        @endforelse
    </x-ui.download-modal>
@endsection
