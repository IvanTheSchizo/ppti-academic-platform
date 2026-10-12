<x-ui.table :headings="[
    ['label' => 'Record Code', 'key' => 'record_code'],
    ['label' => 'Period', 'key' => 'period'],
    ['label' => 'Course ID', 'key' => 'course_id'],
    ['label' => 'Class', 'key' => 'class'],
    ['label' => 'Lecturer Code', 'key' => 'lecturer_code'],
    ['label' => 'Students', 'key' => 'students'],
]">
    @forelse ($courseRecords as $record)
        <tr>
            <td>{{ $record->record_code }}</td>
            <td>{{ $record->period }}</td>
            <td>{{ $record->course_id }}</td>
            <td>{{ $record->class }}</td>
            <td>{{ $record->lecturer_code }}</td>
            <td>{{ $record->students_count }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="6">
                <x-ui.empty-state title="No course records found" description="Try changing the search or filters." icon="search" />
            </td>
        </tr>
    @endforelse

    <x-slot:footer>
        <x-ui.pagination :paginator="$courseRecords" />
    </x-slot:footer>
</x-ui.table>