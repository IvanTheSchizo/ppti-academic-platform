@php
    $statusColors = ['Active' => 'success', 'Inactive' => 'danger'];
@endphp

<x-ui.table :headings="[
    ['label' => 'Lecturer Code', 'key' => 'lecturer_code'],
    'Name',
    'Email',
    'Status',
]">
    @forelse ($lecturers as $lecturer)
        <tr class="cursor-pointer hover:bg-subtle/50 transition-colors" onclick="window.location='{{ route('lecturers.profile', $lecturer->id) }}'">
            <td>{{ $lecturer->lecturer_code }}</td>
            <td class="font-medium text-text">
                {{ $lecturer->name }}
            </td>
            <td>{{ $lecturer->email_binus_edu }}</td>
            <td><x-ui.badge :color="$statusColors[$lecturer->status] ?? 'muted'">{{ $lecturer->status }}</x-ui.badge></td>
        </tr>
    @empty
        <tr>
            <td colspan="4">
                <x-ui.empty-state title="No lecturers found" description="Try changing the search or filters." icon="search" />
            </td>
        </tr>
    @endforelse

    <x-slot:footer>
        {{-- <x-ui.pagination :paginator="$lecturers" /> --}}
    </x-slot:footer>
</x-ui.table>