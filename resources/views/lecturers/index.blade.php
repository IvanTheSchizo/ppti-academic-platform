@extends('layouts.app')

@section('title', 'Lecturer List')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header title="Lecturer List" divider>
            <x-slot:actions>
                <a href="#">
                    <x-ui.button type="button"><x-ui.icon name="download" /> Download</x-ui.button>
                </a>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- 1. Metric Stat Bar --}}
        @include('lecturers.partials.stats', ['stats' => ['total' => 0, 'active' => 0, 'inactive' => 0]])

        {{-- 2. Search & Expandable Filters --}}
        @include('lecturers.partials.filters')

        {{-- 3. Data Table & Pagination Controls --}}
        @include('lecturers.partials.table', ['lecturers' => []])
    </div>
@endsection