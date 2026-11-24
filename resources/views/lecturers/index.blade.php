@extends('layouts.app')

@section('title', 'Lecturer List')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header title="Lecturer List" divider>
            <x-slot:actions>
                <x-ui.button type="button" variant="secondary" data-modal-open="#bulk-lecturer-photo-modal">
                    <x-ui.icon name="cloud_upload" /> Upload Photos
                </x-ui.button>
                <x-ui.button type="button" variant="accent" data-modal-open="#download-lecturers-modal">
                    <x-ui.icon name="download" /> Download
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        @include('lecturers.partials.stats')
        @include('lecturers.partials.filters')
        @include('lecturers.partials.table')
    </div>

    <x-ui.download-modal
        id="download-lecturers-modal"
        title="Download Lecturers"
        :action="route('lecturers.export')"
        :columns="['Batch', 'Lecturer Code', 'Name', 'Email', 'Status']"
        search-placeholder="Search by name, code, batch, or email"
        item-label="lecturer"
    >
        @forelse ($exportLecturers as $lecturer)
            @php
                $batchesText = $lecturer->batchLabels();
                $searchString = strtolower($lecturer->lecturer_code . ' ' . $lecturer->name . ' ' . $batchesText . ' ' . ($lecturer->email_binus_edu ?? '') . ' ' . $lecturer->status);
            @endphp
            <tr data-download-row data-search="{{ $searchString }}" class="cursor-pointer transition-colors hover:bg-page">
                <td class="px-3 py-3 text-center">
                    <input type="checkbox" name="ids[]" value="{{ $lecturer->id }}" data-download-cb class="size-4 shrink-0 cursor-pointer rounded border-border text-primary accent-navy focus:ring-primary">
                </td>
                <td class="px-3 py-3">{{ $batchesText }}</td>
                <td class="px-3 py-3 font-medium text-text">{{ $lecturer->lecturer_code }}</td>
                <td class="px-3 py-3">{{ $lecturer->name }}</td>
                <td class="px-3 py-3">{{ $lecturer->email_binus_edu ?? '-' }}</td>
                <td class="px-3 py-3">{{ $lecturer->status }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="p-6 text-center text-muted">No lecturers found to download.</td>
            </tr>
        @endforelse
    </x-ui.download-modal>

    <x-ui.bulk-photo-modal
        id="bulk-lecturer-photo-modal"
        title="Bulk Upload Lecturer Photos"
        :preview-url="route('lecturers.photos.preview')"
        :commit-url="route('lecturers.photos.commit')"
        example-filename="D0001.jpg"
        match-label="Lecturer Code"
    />
@endsection
