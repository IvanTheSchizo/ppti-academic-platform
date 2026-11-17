@extends('layouts.app')

@section('title', 'Lecturer Profile')

@section('content')
    <div class="space-y-card">
        <x-ui.breadcrumb :items="[
            ['label' => 'Lecturer', 'href' => route('lecturers.index')],
            ['label' => $lecturer->name],
        ]" />

        <x-ui.page-header :title="$lecturer->name" :subtitle="$lecturer->lecturer_code">
            <x-slot:media>
                <button type="button"
                        data-modal-open="#edit-lecturer-photo-modal"
                        aria-label="Change profile photo"
                        title="Change profile photo"
                        class="group relative inline-flex cursor-pointer rounded-full focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                    <x-ui.avatar :src="$lecturer->photoUrl()" :alt="$lecturer->name" />
                    <span class="absolute inset-0 flex items-center justify-center rounded-full bg-text/50 text-on-primary opacity-0 transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100">
                        <x-ui.icon name="pencil" class="size-5" />
                    </span>
                </button>
            </x-slot:media>
            <x-slot:actions>
                @if ($activeTab !== 'courses')
                    <x-ui.button variant="secondary" type="button" data-modal-open="#edit-lecturer-modal">
                        <x-ui.icon name="pencil" /> Edit
                    </x-ui.button>
                    <x-ui.button type="button" variant="accent" :href="route('lecturers.profile.export', [$lecturer->id, 'tab' => $activeTab])">
                        <x-ui.icon name="download" /> Download
                    </x-ui.button>
                @else
                    <x-ui.button type="button" variant="accent" data-modal-open="#download-lecturer-courses-modal">
                        <x-ui.icon name="download" /> Download
                    </x-ui.button>
                @endif
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.tabs :tabs="[
            ['label' => 'Overview', 'href' => route('lecturers.profile', $lecturer->id), 'active' => $activeTab !== 'courses'],
            ['label' => 'Courses', 'href' => route('lecturers.profile', [$lecturer->id, 'tab' => 'courses']), 'active' => $activeTab === 'courses'],
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

        @if ($activeTab === 'courses')
            @include('lecturers.partials.profile-courses')
        @else
            @include('lecturers.partials.profile-overview')
        @endif
    </div>

    @if ($activeTab !== 'courses')
        @include('lecturers.partials.edit-modal')
    @else
        <x-ui.download-modal
            id="download-lecturer-courses-modal"
            title="Download Lecturer Courses"
            :action="route('lecturers.profile.export', $lecturer->id)"
            :params="['tab' => 'courses']"
            :columns="['Period', 'Course ID', 'Course Name', 'Class', 'IKADQ']"
            search-placeholder="Search by course or period"
            item-label="course"
        >
            @forelse ($exportCourses as $course)
                @php
                    $searchString = strtolower($course->period . ' ' . $course->course_id . ' ' . $course->course_name . ' ' . $course->class);
                @endphp
                <tr data-download-row data-search="{{ $searchString }}" class="cursor-pointer transition-colors hover:bg-page">
                    <td class="px-3 py-3 text-center">
                        <input type="checkbox" name="ids[]" value="{{ $course->id }}" data-download-cb class="size-4 shrink-0 cursor-pointer rounded border-border text-primary accent-navy focus:ring-primary">
                    </td>
                    <td class="px-3 py-3">{{ $course->period }}</td>
                    <td class="px-3 py-3 font-medium text-text">{{ $course->course_id }}</td>
                    <td class="px-3 py-3">{{ $course->course_name }}</td>
                    <td class="px-3 py-3 font-medium text-text">{{ $course->class }}</td>
                    <td class="px-3 py-3">{{ number_format($course->ikadq, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-6 text-center text-muted">No courses found to download.</td>
                </tr>
            @endforelse
        </x-ui.download-modal>
    @endif

    <x-ui.photo-modal
        id="edit-lecturer-photo-modal"
        title="Lecturer Profile Photo"
        :src="$lecturer->photoUrl()"
        :alt="$lecturer->name"
        :upload-action="route('lecturers.photo.update', $lecturer->id)"
        :delete-action="route('lecturers.photo.destroy', $lecturer->id)"
    />
@endsection
