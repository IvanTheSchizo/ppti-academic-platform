<div class="space-y-6">
    <form method="GET" action="{{ route('students.profile', $student->id) }}" class="flex gap-4">
        <input type="hidden" name="tab" value="grades">
        <x-form.search-input name="q" :value="request()->query('q')" aria-label="Search grades" class="flex-1" />
    </form>

    <x-ui.table :headings="[
        ['label' => 'Period', 'sortable' => false],
        ['label' => 'Course ID', 'sortable' => false],
        ['label' => 'Course', 'sortable' => false],
        ['label' => 'Lecturer', 'sortable' => false],
        ['label' => 'SCU', 'sortable' => false],
        ['label' => 'Grade', 'sortable' => false],
    ]">
        @forelse ($grades as $grade)
            <tr>
                <td>{{ $grade->period }}</td>
                <td>{{ $grade->course_id }}</td>
                <td>{{ $grade->course_name }}</td>
                <td>
                    @if ($grade->lecturer)
                        <a href="{{ route('lecturers.profile', $grade->lecturer->id) }}" class="rounded-ui hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">{{ $grade->lecturer->name }}</a>
                    @else
                        -
                    @endif
                </td>
                <td>{{ $grade->sks }}</td>
                <td>{{ $grade->grade }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    <x-ui.empty-state title="No grades found" description="This student has no matching grade records." icon="search" />
                </td>
            </tr>
        @endforelse

        <x-slot:footer>
            <x-ui.pagination :paginator="$grades" />
        </x-slot:footer>
    </x-ui.table>
</div>
