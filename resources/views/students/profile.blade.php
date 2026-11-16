@extends('layouts.app')

@section('title', 'Student Profile')

@section('content')
    <div class="space-y-card">
        <x-ui.breadcrumb :items="[
            ['label' => 'Student', 'href' => route('students.index')],
            ['label' => $student->name],
        ]" />

        <x-ui.page-header :title="$student->name" :subtitle="$student->nim">
            <x-slot:media><x-ui.avatar /></x-slot:media>
            <x-slot:actions>
                @if ($activeTab !== 'grades')
                    <x-ui.button variant="secondary" type="button" data-modal-open="#edit-student-modal">
                        <x-ui.icon name="pencil" /> Edit
                    </x-ui.button>
                    <x-ui.button type="button" :href="route('students.profile.export', [$student->id, 'tab' => $activeTab])">
                        <x-ui.icon name="download" /> Download
                    </x-ui.button>
                @else
                    <x-ui.button type="button" data-modal-open="#download-student-grades-modal">
                        <x-ui.icon name="download" /> Download
                    </x-ui.button>
                @endif
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.tabs :tabs="[
            ['label' => 'Overview', 'href' => route('students.profile', $student->id), 'active' => $activeTab !== 'grades'],
            ['label' => 'Grades', 'href' => route('students.profile', [$student->id, 'tab' => 'grades']), 'active' => $activeTab === 'grades'],
        ]" />

        @if ($errors->any())
            <x-ui.alert variant="danger">
                <p class="font-semibold">Please correct the following:</p>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        @if ($activeTab === 'grades')
            @include('students.partials.profile-grades')
        @else
            @include('students.partials.profile-overview')
        @endif
    </div>

    @if ($activeTab !== 'grades')
        @include('students.partials.edit-modal')
    @else
        <x-ui.download-modal
            id="download-student-grades-modal"
            title="Download Student Grades"
            :action="route('students.profile.export', $student->id)"
            :params="['tab' => 'grades']"
            :columns="['Period', 'Course ID', 'Course', 'Lecturer', 'Grade']"
            search-placeholder="Search by course or lecturer"
            item-label="grade"
        >
            @forelse ($exportGrades as $grade)
                @php
                    $lecturerName = $grade->lecturer?->name ?? '-';
                    $searchString = strtolower($grade->period . ' ' . $grade->course_id . ' ' . $grade->course_name . ' ' . $lecturerName . ' ' . ($grade->grade ?? ''));
                @endphp
                <tr data-download-row data-search="{{ $searchString }}" class="cursor-pointer transition-colors hover:bg-page">
                    <td class="px-3 py-3 text-center">
                        <input type="checkbox" name="ids[]" value="{{ $grade->id }}" data-download-cb class="size-4 shrink-0 cursor-pointer rounded border-border text-primary accent-navy focus:ring-primary">
                    </td>
                    <td class="px-3 py-3">{{ $grade->period }}</td>
                    <td class="px-3 py-3 font-medium text-text">{{ $grade->course_id }}</td>
                    <td class="px-3 py-3">{{ $grade->course_name }}</td>
                    <td class="px-3 py-3">{{ $lecturerName }}</td>
                    <td class="px-3 py-3 font-medium text-text">{{ $grade->grade ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-6 text-center text-muted">No grades found to download.</td>
                </tr>
            @endforelse
        </x-ui.download-modal>
    @endif
@endsection
