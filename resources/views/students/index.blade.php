@extends('layouts.app')

@section('title', 'Student List')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header title="Student List" divider>
            <x-slot:actions>
                <x-ui.button type="button" variant="secondary"
                    data-student-edit-toggle
                    aria-pressed="false"
                    aria-controls="student-edit-table">
                    <x-ui.icon name="pencil" />
                    <span data-student-edit-label>Edit List</span>
                </x-ui.button>
                <x-ui.button type="button" data-modal-open="#add-student-modal">
                    <x-ui.icon name="plus" /> Add Student
                </x-ui.button>
                <a href="{{ route('students.export') }}">
                    <x-ui.button type="button" variant="secondary"><x-ui.icon name="download" /> Download</x-ui.button>
                </a>
            </x-slot:actions>
        </x-ui.page-header>

        @if (session('success'))
            <x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>
        @endif

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

        @include('students.partials.stats')
        @include('students.partials.filters')
        @include('students.partials.table')
    </div>

    {{-- Add Student modal --}}
    <x-ui.modal id="add-student-modal" title="Add Student" size="lg">
        <form id="add-student-form" method="POST" action="{{ route('students.store') }}" class="space-y-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-form.label for="add_nim">Student NIM</x-form.label>
                    <x-form.text-input id="add_nim" name="nim" required maxlength="255" />
                </div>
                <div>
                    <x-form.label for="add_name">Full Name</x-form.label>
                    <x-form.text-input id="add_name" name="name" required maxlength="255" />
                </div>
            </div>
            <div>
                <x-form.label for="add_class_id">Batch and Class</x-form.label>
                <x-form.select id="add_class_id" name="class_id" required>
                    <option value="">Select a class</option>
                    @foreach ($classGroups as $classGroup)
                        <option value="{{ $classGroup->id }}">{{ $classGroup->batch?->batch_name }} — {{ $classGroup->class_code }}</option>
                    @endforeach
                </x-form.select>
                <p class="mt-1 text-sm text-muted">Choose an existing class.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-form.label for="add_status">Status</x-form.label>
                    <x-form.select id="add_status" name="status" required>
                        <option value="Active" selected>Active</option>
                        <option value="Graduated">Graduated</option>
                        <option value="On Leave">On Leave</option>
                    </x-form.select>
                </div>
                <div>
                    <x-form.label for="add_cumulative_gpa">Overall GPA (0.00–4.00)</x-form.label>
                    <x-form.text-input id="add_cumulative_gpa" type="number" name="cumulative_gpa" min="0" max="4" step="0.01" value="0.00" />
                    
                </div>
            </div>
        </form>
        <x-slot:footer>
            <x-ui.button type="button" variant="secondary" data-modal-close>Cancel</x-ui.button>
            <x-ui.button type="submit" form="add-student-form">Add Student</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- One shared Edit Student modal, populated from the selected table row --}}
    <x-ui.modal id="edit-student-modal" title="Edit Student" size="lg">
        <form id="edit-student-form" method="POST" action="#" class="space-y-4"
              data-update-url-template="{{ route('students.update', ['student' => '__STUDENT_ID__']) }}">
            @csrf
            @method('PUT')
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-form.label for="edit_nim">Student NIM</x-form.label>
                    <x-form.text-input id="edit_nim" name="nim" required maxlength="255" />
                </div>
                <div>
                    <x-form.label for="edit_name">Full Name</x-form.label>
                    <x-form.text-input id="edit_name" name="name" required maxlength="255" />
                </div>
            </div>
            <div>
                <x-form.label for="edit_class_id">Batch and Class</x-form.label>
                <x-form.select id="edit_class_id" name="class_id" required>
                    <option value="">Select a class</option>
                    @foreach ($classGroups as $classGroup)
                        <option value="{{ $classGroup->id }}">{{ $classGroup->batch?->batch_name }} — {{ $classGroup->class_code }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-form.label for="edit_status">Status</x-form.label>
                    <x-form.select id="edit_status" name="status" required>
                        <option value="Active">Active</option>
                        <option value="Graduated">Graduated</option>
                        <option value="On Leave">On Leave</option>
                    </x-form.select>
                </div>
                <div>
                    <x-form.label for="edit_cumulative_gpa">Overall GPA (0.00–4.00)</x-form.label>
                    <x-form.text-input id="edit_cumulative_gpa" type="number" name="cumulative_gpa" min="0" max="4" step="0.01" />
                </div>
            </div>
        </form>
        <x-slot:footer>
            <x-ui.button type="button" variant="secondary" data-modal-close>Cancel</x-ui.button>
            <x-ui.button type="submit" form="edit-student-form">Save Changes</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
@endsection
