@php
    $statusColors = ['Active' => 'success', 'Graduated' => 'primary', 'On Leave' => 'danger'];
@endphp

<x-ui.table :headings="[
    'Batch',
    ['label' => 'Student ID', 'key' => 'nim'],
    'Name',
    'Status',
    'GPA',
]">
    @forelse ($students as $student)
        <tr>
            <td>{{ $student->classGroup?->batch?->batch_name ?? '-' }}</td>
            <td>{{ $student->nim }}</td>
            <td>{{ $student->name }}</td>
            <td><x-ui.badge :color="$statusColors[$student->status] ?? 'muted'">{{ $student->status }}</x-ui.badge></td>
            <td>{{ number_format($student->cumulative_gpa, 2) }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="5">
                <x-ui.empty-state title="No students found" description="Try changing the search or filters." icon="search" />
            </td>
        </tr>
    @endforelse

    <x-slot:footer>
        <x-ui.pagination :paginator="$students" />
    </x-slot:footer>
</x-ui.table>