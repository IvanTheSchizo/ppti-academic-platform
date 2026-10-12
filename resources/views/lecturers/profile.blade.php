@extends('layouts.app')

@section('title', 'Lecturer Profile')

@section('content')
    <div class="space-y-card">
        <x-ui.breadcrumb :items="[
            ['label' => 'Lecturer', 'href' => route('lecturers.index')],
            ['label' => $lecturer->name],
        ]" />

        <x-ui.page-header :title="$lecturer->name" :subtitle="$lecturer->lecturer_code">
            <x-slot:media><x-ui.avatar /></x-slot:media>
            <x-slot:actions>
                @if ($activeTab !== 'courses')
                    <x-ui.button variant="secondary" type="button" data-modal-open="#edit-lecturer-modal">
                        <x-ui.icon name="pencil" /> Edit
                    </x-ui.button>
                @endif
                <x-ui.button type="button"><x-ui.icon name="download" /> Download</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.tabs :tabs="[
            ['label' => 'Overview', 'href' => route('lecturers.profile', $lecturer->id), 'active' => $activeTab !== 'courses'],
            ['label' => 'Courses', 'href' => route('lecturers.profile', [$lecturer->id, 'tab' => 'courses']), 'active' => $activeTab === 'courses'],
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

        @if ($activeTab === 'courses')
            @include('lecturers.partials.profile-courses')
        @else
            @include('lecturers.partials.profile-overview')
        @endif
    </div>

    @if ($activeTab !== 'courses')
        @include('lecturers.partials.edit-modal')
    @endif
@endsection
