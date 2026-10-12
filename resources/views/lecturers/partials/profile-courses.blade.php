<div class="space-y-6">
    <form method="GET" action="{{ route('lecturers.profile', $lecturer->id) }}" class="flex gap-4">
        <input type="hidden" name="tab" value="courses">
        <x-form.search-input name="q" :value="request()->query('q')" aria-label="Search courses" class="flex-1" />
    </form>

    <x-ui.table :headings="[
        ['label' => 'Period', 'sortable' => false],
        ['label' => 'Course ID', 'sortable' => false],
        ['label' => 'Course Name', 'sortable' => false],
        ['label' => 'Class', 'sortable' => false],
        ['label' => 'IKADQ', 'sortable' => false],
        ['label' => 'Students', 'sortable' => false],
    ]">
        @forelse ($courses as $course)
            <tr>
                <td>{{ $course->period }}</td>
                <td>{{ $course->course_id }}</td>
                <td>{{ $course->course_name }}</td>
                <td>{{ $course->class }}</td>
                <td>{{ number_format($course->ikadq, 2) }}</td>
                <td>{{ $course->students_count }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    <x-ui.empty-state title="No courses found" description="This lecturer has no matching course records." icon="search" />
                </td>
            </tr>
        @endforelse

        <x-slot:footer>
            <x-ui.pagination :paginator="$courses" :per-page-options="[5, 10, 25, 50]" />
        </x-slot:footer>
    </x-ui.table>
</div>
