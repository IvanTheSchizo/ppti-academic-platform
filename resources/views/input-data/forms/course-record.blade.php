<div class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
    <x-form.field name="course_id" label="Course" :required="true">
        <x-form.select name="course_id">
            <option value="">Select Course</option>
            @foreach ($courses as $course)
                <option value="{{ $course->id }}" @selected((string) old('course_id') === (string) $course->id)>{{ $course->course_code }} — {{ $course->course_name }}</option>
            @endforeach
        </x-form.select>
    </x-form.field>

    <x-form.field name="period_id" label="Period" :required="true">
        <x-form.select name="period_id">
            <option value="">Select Period</option>
            @foreach ($periods as $period)
                <option value="{{ $period->id }}" @selected((string) old('period_id') === (string) $period->id)>{{ $period->period_name }}</option>
            @endforeach
        </x-form.select>
    </x-form.field>

    <x-form.field name="class_id" label="Class" :required="true">
        <x-form.select name="class_id">
            <option value="">Select Class</option>
            @foreach ($classGroups as $classGroup)
                <option value="{{ $classGroup->id }}" @selected((string) old('class_id') === (string) $classGroup->id)>{{ $classGroup->class_code }}</option>
            @endforeach
        </x-form.select>
    </x-form.field>

    <x-form.field name="lecturer_id" label="Lecturer" :required="true">
        <x-form.select name="lecturer_id">
            <option value="">Select Lecturer</option>
            @foreach ($lecturers as $lecturer)
                <option value="{{ $lecturer->id }}" @selected((string) old('lecturer_id') === (string) $lecturer->id)>{{ $lecturer->name }}</option>
            @endforeach
        </x-form.select>
    </x-form.field>

    <x-form.field name="ikadq" label="IKADQ">
        <x-form.text-input type="number" name="ikadq" :value="old('ikadq')" placeholder="e.g. 3.00" min="0" max="4" step="0.01" />
    </x-form.field>
</div>

@include('input-data.partials.student-picker', [
    'initialStudents' => $initialStudents,
    'classSelect' => '#class_id',
])
