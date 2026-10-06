@extends('layouts.app')

@section('title', 'Student List')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header title="Student List" divider>
            <x-slot:actions>
                <a href="{{ route('students.export') }}">
                    <x-ui.button type="button"><x-ui.icon name="download" /> Download</x-ui.button>
                </a>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- 1. Metric Stat Bar --}}
        @include('students.partials.stats')

        {{-- 2. Search & Expandable Filters --}}
        @include('students.partials.filters')

        {{-- 3. Data Table & Pagination Controls --}}
        @include('students.partials.table')
    </div>
@endsection