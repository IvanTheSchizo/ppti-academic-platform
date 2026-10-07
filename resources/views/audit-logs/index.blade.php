@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header title="Audit Log" divider>
            <x-slot:actions>
                <a href="{{ route('audit-logs.export') }}">
                    <x-ui.button type="button"><x-ui.icon name="download" /> Download</x-ui.button>
                </a>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Filter Bar --}}
        @include('audit-logs.partials.filters')

        {{-- Audit Log Table --}}
        @include('audit-logs.partials.table')
    </div>
@endsection