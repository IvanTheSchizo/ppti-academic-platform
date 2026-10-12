{{-- Edit Course Record modal. Expects $record, $courses, $periods, $classGroups and $lecturers. --}}
<x-ui.modal id="edit-record-modal" title="Edit Course Record" size="lg">
    <form method="POST" action="{{ route('course-records.update', $record->id) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="grid gap-4 sm:grid-cols-2">
            <x-form.field name="course_id" label="Course" :required="true">
                <x-form.select name="course_id" required>
                    <option value="">Select Course</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" @selected((string) old('course_id', $record->course_id) === (string) $course->id)>{{ $course->course_code }} — {{ $course->course_name }}</option>
                    @endforeach
                </x-form.select>
            </x-form.field>

            <x-form.field name="period_id" label="Period" :required="true">
                <x-form.select name="period_id" required>
                    <option value="">Select Period</option>
                    @foreach ($periods as $period)
                        <option value="{{ $period->id }}" @selected((string) old('period_id', $record->period_id) === (string) $period->id)>{{ $period->period_name }}</option>
                    @endforeach
                </x-form.select>
            </x-form.field>

            <x-form.field name="class_id" label="Class" :required="true">
                <x-form.select name="class_id" required>
                    <option value="">Select Class</option>
                    @foreach ($classGroups as $classGroup)
                        <option value="{{ $classGroup->id }}" @selected((string) old('class_id', $record->class_id) === (string) $classGroup->id)>{{ $classGroup->class_code }}</option>
                    @endforeach
                </x-form.select>
            </x-form.field>

            <x-form.field name="lecturer_id" label="Lecturer" :required="true">
                <x-form.select name="lecturer_id" required>
                    <option value="">Select Lecturer</option>
                    @foreach ($lecturers as $lecturer)
                        <option value="{{ $lecturer->id }}" @selected((string) old('lecturer_id', $record->lecturer_id) === (string) $lecturer->id)>{{ $lecturer->name }}</option>
                    @endforeach
                </x-form.select>
            </x-form.field>

            <x-form.field name="ikadq" label="IKADQ">
                <x-form.text-input type="number" name="ikadq" :value="old('ikadq', $record->lecturerPerformance?->ikadq)" placeholder="e.g. 3.00" min="0" max="4" step="0.01" />
            </x-form.field>
        </div>

        <div class="flex justify-end gap-3 border-t border-border pt-4">
            <x-ui.button type="button" variant="secondary" data-modal-close>Cancel</x-ui.button>
            <x-ui.button type="submit">Save Changes</x-ui.button>
        </div>
    </form>
</x-ui.modal>
