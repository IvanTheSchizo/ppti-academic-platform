@extends('layouts.app')

@section('title', 'Student List')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header title="Student List" divider>
            <x-slot:actions>
                <x-ui.button type="button" variant="secondary" data-modal-open="#bulk-student-photo-modal">
                    <x-ui.icon name="cloud_upload" /> Upload Photos
                </x-ui.button>
                <x-ui.button type="button" data-modal-open="#download-students-modal">
                    <x-ui.icon name="download" /> Download
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        @include('students.partials.stats')
        @include('students.partials.filters')
        @include('students.partials.table')
    </div>

    <x-ui.download-modal
        id="download-students-modal"
        title="Download Students"
        :action="route('students.export')"
        :columns="['NIM', 'Name', 'Batch', 'Class', 'Status']"
        search-placeholder="Search by name, NIM, or class"
        item-label="student"
    >
        @forelse ($exportStudents as $student)
            @php
                $batchName = $student->classGroup?->batch?->batch_name ?? '-';
                $classCode = $student->classGroup?->class_code ?? '-';
                $searchString = strtolower($student->nim . ' ' . $student->name . ' ' . $batchName . ' ' . $classCode . ' ' . $student->status);
            @endphp
            <tr data-download-row data-search="{{ $searchString }}" class="cursor-pointer transition-colors hover:bg-page">
                <td class="px-3 py-3 text-center">
                    <input type="checkbox" name="ids[]" value="{{ $student->id }}" data-download-cb class="size-4 shrink-0 cursor-pointer rounded border-border text-primary accent-navy focus:ring-primary">
                </td>
                <td class="px-3 py-3 font-medium text-text">{{ $student->nim }}</td>
                <td class="px-3 py-3">{{ $student->name }}</td>
                <td class="px-3 py-3">{{ $batchName }}</td>
                <td class="px-3 py-3 font-medium text-text">{{ $classCode }}</td>
                <td class="px-3 py-3">{{ $student->status }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="p-6 text-center text-muted">No students found to download.</td>
            </tr>
        @endforelse
    </x-ui.download-modal>

    <x-ui.bulk-photo-modal
        id="bulk-student-photo-modal"
        title="Bulk Upload Student Photos"
        :preview-url="route('students.photos.preview')"
        :commit-url="route('students.photos.commit')"
        example-filename="2700000001.jpg"
        match-label="Student ID"
    />
@endsection
