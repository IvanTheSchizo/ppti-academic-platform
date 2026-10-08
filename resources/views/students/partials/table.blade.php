@php
    $statusColors = [
        'Active' => 'success',
        'Graduated' => 'primary',
        'On Leave' => 'danger',
    ];
@endphp

<x-ui.table :headings="[
    'Batch',
    ['label' => 'Student ID', 'key' => 'nim'],
    'Name',
    'Status',
    'GPA',
    'Actions'
]">

    @forelse ($students as $student)

        <tr>

            <td>
                {{ $student->batch?->batch_name ?? '-' }}
            </td>

            <td>
                {{ $student->nim }}
            </td>

            <td>
                {{ $student->name }}
            </td>

            <td>
                <x-ui.badge :color="$statusColors[$student->status] ?? 'muted'">
                    {{ $student->status }}
                </x-ui.badge>
            </td>

            <td>
                {{ number_format($student->cumulative_gpa, 2) }}
            </td>

            {{-- Hidden until Edit List is pressed --}}
            <td class="action-cell">

                <x-ui.button
                    type="button"
                    size="sm"
                    color="secondary"
                    onclick="openEditModal(
                        {{ Js::from([
                            'id' => $student->id,
                            'nim' => $student->nim,
                            'name' => $student->name,
                            'status' => $student->status,
                            'cumulative_gpa' => (string) $student->cumulative_gpa,
                            'batch_id' => $student->batch_id,
                        ]) }}
                    )"
                >
                    <x-ui.icon name="pencil" />
                </x-ui.button>

            </td>

        </tr>

    @empty

        <tr>
            <td colspan="6">

                <x-ui.empty-state
                    title="No students found"
                    description="Try changing the search or filters."
                    icon="search"
                />

            </td>
        </tr>

    @endforelse

    <x-slot:footer>
        <x-ui.pagination :paginator="$students" />
    </x-slot:footer>

</x-ui.table>