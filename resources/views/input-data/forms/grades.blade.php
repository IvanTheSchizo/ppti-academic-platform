<div class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
    <x-form.field name="course_record_id" label="Course" :required="true">
        <x-form.select name="course_record_id">
            <option value="">Select Course</option>
            @foreach ($records as $record)
                <option value="{{ $record->id }}" @selected((string) old('course_record_id') === (string) $record->id)>
                    {{ $record->course?->course_code }} — {{ $record->course?->course_name }} ({{ $record->period?->period_name }}, {{ $record->classGroup?->class_code }})
                </option>
            @endforeach
        </x-form.select>
    </x-form.field>
</div>

@include('input-data.partials.student-picker', [
    'withGrade' => true,
    'initialStudents' => $initialStudents,
    'courseSelect' => '#course_record_id',
])
