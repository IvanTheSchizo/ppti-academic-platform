@extends('layouts.app')

@section('title', 'Lecturer List')

@section('content')
    <div class="space-y-card">
        <x-ui.page-header title="Lecturer List" divider />

        <x-ui.card>
            <x-ui.empty-state title="Lecturer List" description="This page is a placeholder." icon="badge" />
        </x-ui.card>
    </div>
@endsection
