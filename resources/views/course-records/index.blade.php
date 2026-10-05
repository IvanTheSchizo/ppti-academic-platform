@extends('layouts.app')

@section('title', 'Course Records')

@section('content')
    <div class="space-y-card">
        <x-ui.page-header title="Course Records" divider/>

        <x-ui.card>
            <x-ui.empty-state title="Course Records" description="This page is a placeholder." icon="school" />
        </x-ui.card>
    </div>
@endsection
