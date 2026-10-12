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

        @include('students.partials.stats')
        @include('students.partials.filters')
        @include('students.partials.table')
    </div>
@endsection
