{{-- One Semester GPA row. $index may be the "__INDEX__" placeholder used by the JS template. --}}
<div data-semester-row class="flex items-center gap-3">
    <x-form.select name="semesters[{{ $index }}][period_id]" id="semesters-{{ $index }}-period" aria-label="Semester" required class="min-w-0 flex-1">
        <option value="">Select Semester</option>
        @foreach ($periods as $period)
            <option value="{{ $period->id }}" @selected((string) $periodId === (string) $period->id)>{{ $period->period_name }}</option>
        @endforeach
    </x-form.select>

    <x-form.text-input type="number" name="semesters[{{ $index }}][gpa]" id="semesters-{{ $index }}-gpa" :value="$gpa" aria-label="Semester GPA" placeholder="0.00" min="0" max="4" step="0.01" required data-semester-gpa-input class="w-32! shrink-0 text-center" />

    <button type="button" data-semester-remove aria-label="Remove semester"
            class="grid size-8 shrink-0 place-items-center rounded-ui text-muted transition-colors hover:text-danger active:bg-border/50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
        <x-ui.icon name="delete" />
    </button>
</div>
