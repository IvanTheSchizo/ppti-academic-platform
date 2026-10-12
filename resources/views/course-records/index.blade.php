@extends('layouts.app')

@section('title', 'Course Records')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header title="Course Records" divider>
            <x-slot:actions>
                <a href="{{ route('course-records.export') }}">
                    <x-ui.button type="button"><x-ui.icon name="download" /> Download</x-ui.button>
                </a>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Search & Expandable Filters --}}
        @include('course-records.partials.filters')

        {{-- Data Table & Pagination Controls --}}
        @include('course-records.partials.table')
    </div>
@endsection