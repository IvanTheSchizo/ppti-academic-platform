{{-- Edit Student modal (profile page). Expects $student, $classGroups and $periods. --}}
@php
    $semesterRows = old('semesters', $student->semesterGpas->map(fn ($row) => [
        'period_id' => $row->period_id,
        'gpa' => number_format((float) $row->gpa, 2, '.', ''),
    ])->all());
@endphp

<x-ui.modal id="edit-student-modal" title="Edit Student" size="lg">
    <form method="POST" action="{{ route('students.update', $student->id) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="grid gap-4 sm:grid-cols-2">
            <x-form.field name="nim" label="NIM" :caps="true">
                <x-form.text-input name="nim" :value="old('nim', $student->nim)" required maxlength="255" autocomplete="off" />
            </x-form.field>

            <x-form.field name="name" label="Full Name" :caps="true">
                <x-form.text-input name="name" :value="old('name', $student->name)" required maxlength="255" autocomplete="name" />
            </x-form.field>

            <x-form.field name="batch" label="Batch" :caps="true">
                {{-- Batch follows the selected class --}}
                <x-form.text-input id="batch" :value="$student->classGroup?->batch?->batch_name" readonly tabindex="-1" data-batch-output class="bg-page" />
            </x-form.field>

            <x-form.field name="class_id" label="Class" :caps="true">
                <x-form.select name="class_id" required data-class-select>
                    <option value="">Select Class</option>
                    @foreach ($classGroups as $classGroup)
                        <option value="{{ $classGroup->id }}" data-batch="{{ $classGroup->batch?->batch_name }}" @selected((string) old('class_id', $student->class_id) === (string) $classGroup->id)>{{ $classGroup->class_code }}</option>
                    @endforeach
                </x-form.select>
            </x-form.field>

            <x-form.field name="status" label="Status" :caps="true">
                <x-form.select name="status" required>
                    @foreach (['Active', 'Graduated', 'On Leave'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $student->status) === $status)>{{ $status }}</option>
                    @endforeach
                </x-form.select>
            </x-form.field>

            <x-form.field name="gender" label="Gender" :caps="true">
                <x-form.select name="gender">
                    <option value="">Select Gender</option>
                    @foreach (['Male', 'Female'] as $gender)
                        <option value="{{ $gender }}" @selected(old('gender', $student->gender) === $gender)>{{ $gender }}</option>
                    @endforeach
                </x-form.select>
            </x-form.field>

            <x-form.field name="email" label="Email" :caps="true">
                <x-form.text-input type="email" name="email" :value="old('email', $student->email)" maxlength="255" placeholder="name@example.com" autocomplete="email" />
            </x-form.field>

            <x-form.field name="phone_number" label="Phone Number" :caps="true">
                <x-form.text-input name="phone_number" :value="old('phone_number', $student->phone_number)" maxlength="30" placeholder="+62" autocomplete="tel" />
            </x-form.field>

            <x-form.field name="date_of_birth" label="Date of Birth" :caps="true">
                <x-form.text-input type="date" name="date_of_birth" :value="old('date_of_birth', $student->date_of_birth?->format('Y-m-d'))" />
            </x-form.field>
        </div>

        {{-- Semester GPA --}}
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

            @error('semesters.*.period_id')<p class="mt-3 text-sm text-danger">{{ $message }}</p>@enderror

            <template data-semester-template>
                @include('students.partials.semester-row', ['index' => '__INDEX__', 'periodId' => '', 'gpa' => ''])
            </template>

            <div class="mt-4 flex items-center justify-between border-t border-border pt-4">
                <span class="text-muted">Cumulative GPA (calculated):</span>
                <span data-semester-cumulative class="font-semibold text-text">-</span>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <x-ui.button type="button" variant="secondary" data-modal-close>Cancel</x-ui.button>
            <x-ui.button type="submit">Save Changes</x-ui.button>
        </div>
    </form>
</x-ui.modal>
