@extends('layouts.app')

@section('title', 'Course Records')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header title="Course Records" divider>
            <x-slot:actions>
                <x-ui.button type="button" data-modal-open="#download-course-records-modal">
                    <x-ui.icon name="download" /> Download
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Search & Expandable Filters --}}
        @include('course-records.partials.filters')

        {{-- Data Table & Pagination Controls --}}
        @include('course-records.partials.table')
    </div>

    <x-ui.download-modal
        id="download-course-records-modal"
        title="Download Course Records"
        :action="route('course-records.export')"
        :columns="['Record Code', 'Period', 'Course ID', 'Class', 'Lecturer Code']"
        search-placeholder="Search by record code, course, or lecturer"
        item-label="course record"
    >
        @forelse ($exportCourseRecords as $record)
            @php
                $searchString = strtolower($record->record_code . ' ' . $record->period . ' ' . $record->course_id . ' ' . $record->class . ' ' . $record->lecturer_code);
            @endphp
            <tr data-download-row data-search="{{ $searchString }}" class="cursor-pointer transition-colors hover:bg-page">
                <td class="px-3 py-3 text-center">
                    <input type="checkbox" name="ids[]" value="{{ $record->id }}" data-download-cb class="size-4 shrink-0 cursor-pointer rounded border-border text-primary accent-navy focus:ring-primary">
                </td>
                <td class="px-3 py-3 font-medium text-text">{{ $record->record_code }}</td>
                <td class="px-3 py-3">{{ $record->period }}</td>
                <td class="px-3 py-3">{{ $record->course_id }}</td>
                <td class="px-3 py-3 font-medium text-text">{{ $record->class }}</td>
                <td class="px-3 py-3">{{ $record->lecturer_code }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="p-6 text-center text-muted">No course records found to download.</td>
            </tr>
        @endforelse
    </x-ui.download-modal>
@endsection
