@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
    <div class="space-y-card">
        <x-ui.page-header title="Audit Log" divider />

        <x-ui.card>
            <x-ui.empty-state title="Audit Log" description="This page is a placeholder." icon="history" />
        </x-ui.card>
    </div>
@endsection
