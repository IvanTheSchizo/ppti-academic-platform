@php
    $statusColors = ['Active' => 'success', 'Graduated' => 'primary', 'On Leave' => 'danger'];
@endphp

<div id="student-edit-table" data-student-edit-table data-student-edit-mode="off">
<x-ui.table :headings="[
    'Batch',
    ['label' => 'Student ID', 'key' => 'nim'],
    'Name',
    'Class',
    'Status',
    'GPA',
    'Actions',
]">
    @forelse ($students as $student)
        <tr>
            <td>{{ $student->classGroup?->batch?->batch_name ?? '-' }}</td>
            <td>{{ $student->nim }}</td>
            <td>{{ $student->name }}</td>
            <td>{{ $student->classGroup?->class_code ?? '-' }}</td>
            <td><x-ui.badge :color="$statusColors[$student->status] ?? 'muted'">{{ $student->status }}</x-ui.badge></td>
            <td>{{ number_format((float) $student->cumulative_gpa, 2) }}</td>
            <td>
                <x-ui.button type="button" variant="secondary" size="sm"
                    data-edit-student
                    data-student-id="{{ $student->id }}"
                    data-nim="{{ $student->nim }}"
                    data-name="{{ $student->name }}"
                    data-class-id="{{ $student->class_id }}"
                    data-status="{{ $student->status }}"
                    data-gpa="{{ $student->cumulative_gpa }}">
                    <x-ui.icon name="pencil" /> Edit
                </x-ui.button>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7">
                <x-ui.empty-state title="No students found" description="Try changing the search or filters." icon="search" />
            </td>
        </tr>
    @endforelse

    <x-slot:footer>
        <x-ui.pagination :paginator="$students" />
    </x-slot:footer>
</x-ui.table>
</div>
