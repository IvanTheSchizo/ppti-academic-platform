@extends('layouts.app')

@section('title', 'Input Data')

@php
    // tab => [label, icon, card title, submit label]
    $tabs = [
        // Keep tabs in dependency/workflow order; route keys and form behavior stay unchanged.
        'batch' => ['Batch', 'work', 'New Batch', 'Add Batch'],
        'class' => ['Class', 'groups', 'New Class', 'Add Class'],
        'student' => ['Student', 'person', 'New Student', 'Add Student'],
        'period' => ['Period', 'date_range', 'New Period', 'Add Period'],
        'lecturer' => ['Lecturer', 'badge', 'New Lecturer', 'Add Lecturer'],
        'course' => ['Course', 'menu_book', 'New Course', 'Add Course'],
        'course-record' => ['Course Record', 'school', 'New Course Record', 'Add Course Record'],
        'grades' => ['Grades', 'assignment', 'New Grades', 'Save Grades'],
    ];

    [, , $cardTitle, $submitLabel] = $tabs[$tab];
@endphp

@section('content')
    <div class="space-y-6">
        <x-ui.page-header title="Input Data">
            <x-slot:actions>
                @if (in_array($tab, \App\Http\Controllers\InputDataController::IMPORT_TABS, true))
                    <x-ui.button type="button" variant="secondary" data-modal-open="#import-modal">
                        <x-ui.icon name="upload_file" /> Import CSV
                    </x-ui.button>
                @endif
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

    @if (in_array($tab, \App\Http\Controllers\InputDataController::IMPORT_TABS, true))
        @include('input-data.partials.import-modal')
    @endif

    @if ($tab === 'batch')
        <x-ui.card class="mt-6">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-section font-semibold text-text">Existing Batches</h2>
                <span class="text-body text-muted">{{ $batchesList->count() }} total</span>
            </div>
            @if ($batchesList->isEmpty())
                <p class="py-6 text-center text-body text-muted">No batches yet. Add your first batch using the form above.</p>
            @else
                <x-ui.table :headings="[['label' => 'Batch Name', 'sortable' => false], ['label' => 'Classes', 'sortable' => false]]">
                    @foreach ($batchesList as $batch)
                        <tr>
                            <td>{{ $batch->batch_name }}</td>
                            <td>{{ $batch->classes_count }}</td>
                        </tr>
                    @endforeach
                </x-ui.table>
            @endif
        </x-ui.card>
    @elseif ($tab === 'period')
        <x-ui.card class="mt-6">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-section font-semibold text-text">Existing Periods</h2>
                <span class="text-body text-muted">{{ $periodsList->count() }} total</span>
            </div>
            @if ($periodsList->isEmpty())
                <p class="py-6 text-center text-body text-muted">No periods yet. Add a period using the form above.</p>
            @else
                <x-ui.table :headings="[['label' => 'Period', 'sortable' => false], ['label' => 'Start Date', 'sortable' => false], ['label' => 'End Date', 'sortable' => false], ['label' => 'Course Records', 'sortable' => false]]">
                    @foreach ($periodsList as $period)
                        <tr>
                            <td>{{ $period->period_name }}</td>
                            <td>{{ $period->start_date->format('d M Y') }}</td>
                            <td>{{ $period->end_date?->format('d M Y') ?? 'Ongoing' }}</td>
                            <td>{{ $period->course_records_count }}</td>
                        </tr>
                    @endforeach
                </x-ui.table>
            @endif
        </x-ui.card>
    @endif
@endsection
