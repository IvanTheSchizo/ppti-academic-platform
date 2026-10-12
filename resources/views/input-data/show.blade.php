@extends('layouts.app')

@section('title', 'Input Data')

@php
    // tab => [label, icon, card title, submit label]
    $tabs = [
        'student' => ['Student', 'person', 'New Student', 'Add Student'],
        'lecturer' => ['Lecturer', 'badge', 'New Lecturer', 'Add Lecturer'],
        'course' => ['Course', 'menu_book', 'New Course', 'Add Course'],
        'class' => ['Class', 'groups', 'New Class', 'Add Class'],
        'course-record' => ['Course Record', 'school', 'New Course Record', 'Add Course Record'],
        'grades' => ['Grades', 'assignment', 'New Grades', 'Save Grades'],
    ];

    [, , $cardTitle, $submitLabel] = $tabs[$tab];
@endphp

@section('content')
    <div class="space-y-6">
        <x-ui.page-header title="Input Data">
            <x-slot:actions>
                <x-ui.button type="button" variant="secondary" data-modal-open="#import-modal">
                    <x-ui.icon name="upload_file" /> Import CSV
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.tabs variant="navy" :tabs="collect($tabs)->map(fn ($config, $key) => [
            'label' => $config[0],
            'icon' => $config[1],
            'href' => route('input-data.show', $key),
            'active' => $key === $tab,
        ])->values()->all()" />

        <x-ui.card>
            <h2 class="mb-6 border-b border-border pb-4 text-section font-semibold text-text">{{ $cardTitle }}</h2>

            @if ($errors->any())
                <x-ui.alert variant="danger" class="mb-6">
                    <p class="font-semibold">Please correct the following:</p>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach (array_unique($errors->all()) as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-ui.alert>
            @endif

            <form method="POST" action="{{ route('input-data.store', $tab) }}" novalidate>
                @csrf

                <div class="min-h-96 space-y-6">
                    @include('input-data.forms.' . $tab)
                </div>

                <div class="mt-8 flex justify-end gap-3 border-t border-border pt-6">
                    <x-ui.button variant="secondary" :href="route('input-data.show', $tab)">Clear</x-ui.button>
                    <x-ui.button type="submit">{{ $submitLabel }}</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

    @include('input-data.partials.import-modal')
@endsection
