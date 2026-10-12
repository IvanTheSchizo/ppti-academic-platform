@extends('layouts.app')

@section('title', 'Student Profile')

@section('content')
    <div class="space-y-card">
        <x-ui.breadcrumb :items="[
            ['label' => 'Student', 'href' => route('students.index')],
            ['label' => $student->name],
        ]" />

        <x-ui.page-header :title="$student->name" :subtitle="$student->nim">
            <x-slot:media><x-ui.avatar /></x-slot:media>
            <x-slot:actions>
                @if ($activeTab !== 'grades')
                    <x-ui.button variant="secondary" type="button" data-modal-open="#edit-student-modal">
                        <x-ui.icon name="pencil" /> Edit
                    </x-ui.button>
                @endif
                <x-ui.button type="button" :href="route('students.profile.export', [$student->id, 'tab' => $activeTab])">
                    <x-ui.icon name="download" /> Download
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.tabs :tabs="[
            ['label' => 'Overview', 'href' => route('students.profile', $student->id), 'active' => $activeTab !== 'grades'],
            ['label' => 'Grades', 'href' => route('students.profile', [$student->id, 'tab' => 'grades']), 'active' => $activeTab === 'grades'],
        ]" />

        @if ($errors->any())
            <x-ui.alert variant="danger">
                <p class="font-semibold">Please correct the following:</p>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        @if ($activeTab === 'grades')
            @include('students.partials.profile-grades')
        @else
            @include('students.partials.profile-overview')
        @endif
    </div>

    @if ($activeTab !== 'grades')
        @include('students.partials.edit-modal')
    @endif
@endsection
