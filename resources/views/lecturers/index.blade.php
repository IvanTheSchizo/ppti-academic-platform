@extends('layouts.app')

@section('title', 'Lecturer List')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header title="Lecturer List" divider>
            <x-slot:actions>
                <a href="{{ route('lecturers.export') }}">
                    <x-ui.button type="button"><x-ui.icon name="download" /> Download</x-ui.button>
                </a>
            </x-slot:actions>
        </x-ui.page-header>

        @include('lecturers.partials.stats')
        @include('lecturers.partials.filters')
        @include('lecturers.partials.table')
    </div>
@endsection
