@extends('layouts.app')

@section('title', 'Lecturer List')

@section('content')
    <div class="space-y-4">
        <x-ui.page-header title="Lecturer List" divider>
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-3">
                    <x-ui.button type="button" variant="secondary" data-lecturer-edit-toggle aria-pressed="false" aria-controls="lecturer-table">
                        <x-ui.icon name="pencil" /> <span data-lecturer-edit-label>Edit List</span>
                    </x-ui.button>
                    <x-ui.button type="button" data-modal-open="#add-lecturer-modal">
                        <x-ui.icon name="plus" /> Add Lecturer
                    </x-ui.button>
                    <a href="{{ route('lecturers.export') }}">
                        <x-ui.button type="button" variant="secondary"><x-ui.icon name="download" /> Download</x-ui.button>
                    </a>
                </div>
            </x-slot:actions>
        </x-ui.page-header>

        @include('lecturers.partials.stats')

        @include('lecturers.partials.filters')
        @include('lecturers.partials.table')
    </div>

    <x-ui.modal id="add-lecturer-modal" title="Add Lecturer" size="lg">
        <form id="add-lecturer-form" method="POST" action="{{ route('lecturers.store') }}" class="space-y-5">
            @csrf
            @include('lecturers.partials.form-fields', ['formId' => 'add-lecturer-form', 'lecturer' => null])
            <div class="flex justify-end gap-3 border-t border-border pt-4">
                <x-ui.button type="button" variant="secondary" data-modal-close>Cancel</x-ui.button>
                <x-ui.button type="submit">Save Lecturer</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    <x-ui.modal id="edit-lecturer-modal" title="Edit Lecturer" size="lg">
        <form id="edit-lecturer-form" method="POST" action="" class="space-y-5" data-update-url-template="{{ url('/lecturers/__LECTURER_ID__') }}">
            @csrf
            @method('PUT')
            @include('lecturers.partials.form-fields', ['formId' => 'edit-lecturer-form', 'lecturer' => null])
            <div class="flex justify-end gap-3 border-t border-border pt-4">
                <x-ui.button type="button" variant="secondary" data-modal-close>Cancel</x-ui.button>
                <x-ui.button type="submit">Save Changes</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
@endsection
