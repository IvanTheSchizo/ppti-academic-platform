@php
    $semesterRows = old('semesters', [['period_id' => '', 'gpa' => ''], ['period_id' => '', 'gpa' => '']]);
@endphp

<div class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
    <x-form.field name="nim" label="NIM" :required="true">
        <x-form.text-input name="nim" :value="old('nim')" placeholder="e.g. 2201581234" maxlength="255" autocomplete="off" />
    </x-form.field>

    <x-form.field name="name" label="Full Name" :required="true">
        <x-form.text-input name="name" :value="old('name')" placeholder="e.g. Ayu Wijaya" maxlength="255" autocomplete="off" />
    </x-form.field>

    <x-form.field name="batch_id" label="Batch">
        <x-form.select name="batch_id" data-batch-select>
            <option value="">Select Batch</option>
            @foreach ($batches as $batch)
                <option value="{{ $batch->id }}" @selected((string) old('batch_id') === (string) $batch->id)>{{ $batch->batch_name }}</option>
            @endforeach
        </x-form.select>
    </x-form.field>

    <x-form.field name="class_id" label="Class" :required="true">
        <x-form.select name="class_id" data-class-select data-filtered-by-batch>
            <option value="">Select Class</option>
            @foreach ($classGroups as $classGroup)
                <option value="{{ $classGroup->id }}" data-batch-id="{{ $classGroup->batch_id }}" @selected((string) old('class_id') === (string) $classGroup->id)>{{ $classGroup->class_code }}</option>
            @endforeach
        </x-form.select>
    </x-form.field>

    <x-form.field name="status" label="Status">
        <x-form.select name="status">
            @foreach (['Active', 'Graduated', 'On Leave'] as $status)
                <option value="{{ $status }}" @selected(old('status', 'Active') === $status)>{{ $status }}</option>
            @endforeach
        </x-form.select>
    </x-form.field>

    <x-form.field name="gender" label="Gender">
        <x-form.select name="gender">
            <option value="">Select Gender</option>
            @foreach (['Male', 'Female'] as $gender)
                <option value="{{ $gender }}" @selected(old('gender') === $gender)>{{ $gender }}</option>
            @endforeach
        </x-form.select>
    </x-form.field>

    <x-form.field name="email" label="Email">
        <x-form.text-input type="email" name="email" :value="old('email')" placeholder="name@example.com" maxlength="255" autocomplete="off" />
    </x-form.field>

    <x-form.field name="phone_number" label="Phone Number">
        <x-form.text-input name="phone_number" :value="old('phone_number')" placeholder="+62" maxlength="30" autocomplete="off" />
    </x-form.field>

    <x-form.field name="date_of_birth" label="Date of Birth">
        <x-form.text-input type="date" name="date_of_birth" :value="old('date_of_birth')" />
    </x-form.field>
</div>

{{-- Semester GPA (behaviour shared with the Edit Student modal) --}}
<div data-semester-gpa class="rounded-ui border border-border p-4">
    <div class="mb-4 flex items-center justify-between gap-4">
        <h3 class="font-semibold text-text">Semester GPA</h3>
        <x-ui.button type="button" variant="secondary" size="sm" data-semester-add>
            <x-ui.icon name="plus" /> Add Semester
        </x-ui.button>
    </div>

    <div data-semester-rows class="space-y-3">
        @foreach ($semesterRows as $index => $row)
            @include('students.partials.semester-row', ['index' => $index, 'periodId' => $row['period_id'] ?? '', 'gpa' => $row['gpa'] ?? ''])
        @endforeach
    </div>

    <template data-semester-template>
        @include('students.partials.semester-row', ['index' => '__INDEX__', 'periodId' => '', 'gpa' => ''])
    </template>

    <div class="mt-4 flex items-center justify-between border-t border-border pt-4">
        <span class="text-muted">Cumulative GPA (calculated):</span>
        <span data-semester-cumulative class="font-semibold text-text">-</span>
    </div>
</div>
