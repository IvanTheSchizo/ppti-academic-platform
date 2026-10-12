@php
    $statusColors = ['Active' => 'success', 'Graduated' => 'primary', 'On Leave' => 'danger'];
@endphp

<x-ui.table :headings="[
    'Batch',
    ['label' => 'Student ID', 'key' => 'nim'],
    'Name',
    'Class',
    'Status',
    'GPA',
]">
    @forelse ($students as $student)
        <tr data-row-href="{{ route('students.profile', $student->id) }}" class="cursor-pointer">
            <td>{{ $student->classGroup?->batch?->batch_name ?? '-' }}</td>
            <td>{{ $student->nim }}</td>
            <td>{{ $student->name }}</td>
            <td>{{ $student->classGroup?->class_code ?? '-' }}</td>
            <td><x-ui.badge :color="$statusColors[$student->status] ?? 'muted'">{{ $student->status }}</x-ui.badge></td>
            <td>{{ number_format((float) $student->cumulative_gpa, 2) }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="6">
                <x-ui.empty-state title="No students found" description="Try changing the search or filters." icon="search" />
            </td>
        </tr>
    @endforelse

    <x-slot:footer>
        <x-ui.pagination :paginator="$students" />
    </x-slot:footer>
</x-ui.table>
