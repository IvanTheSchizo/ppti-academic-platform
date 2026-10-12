@php
    $statusColors = ['Active' => 'success', 'Inactive' => 'danger', 'On Leave' => 'muted'];
@endphp

<x-ui.table id="lecturer-table" :headings="[
    ['label' => 'Lecturer Code', 'key' => 'lecturer_code'],
    ['label' => 'Name', 'key' => 'name'],
    ['label' => 'Email', 'key' => 'email_binus_edu'],
    ['label' => 'Status', 'key' => 'status'],
    ['label' => 'Actions', 'sortable' => false],
]" data-lecturer-edit-table data-lecturer-edit-mode="off">
    @forelse ($lecturers as $lecturer)
        <tr class="cursor-pointer hover:bg-subtle/50 transition-colors"
            onclick="if (!event.target.closest('[data-edit-lecturer]')) { window.location='{{ route('lecturers.profile', $lecturer->id) }}'; }">
            <td>{{ $lecturer->lecturer_code }}</td>
            <td class="font-medium text-text">{{ $lecturer->name }}</td>
            <td>{{ $lecturer->email_binus_edu }}</td>
            <td><x-ui.badge :color="$statusColors[$lecturer->status] ?? 'muted'">{{ $lecturer->status }}</x-ui.badge></td>
            <td>
                <x-ui.button type="button" size="sm" variant="secondary" data-edit-lecturer
                    data-lecturer-id="{{ $lecturer->id }}"
                    data-nip="{{ $lecturer->nip }}"
                    data-lecturer-code="{{ $lecturer->lecturer_code }}"
                    data-name="{{ $lecturer->name }}"
                    data-email-binus-edu="{{ $lecturer->email_binus_edu }}"
                    data-email-binus-ac-id="{{ $lecturer->email_binus_ac_id }}"
                    data-phone-number="{{ $lecturer->phone_number }}"
                    data-jja="{{ $lecturer->jja }}"
                    data-latest-education="{{ $lecturer->latest_education }}"
                    data-status="{{ $lecturer->status }}">
                    <x-ui.icon name="pencil" /> Edit
                </x-ui.button>
            </td>
        </tr>
    @empty
        <tr><td colspan="5"><x-ui.empty-state title="No lecturers found" description="Try changing the search or filters." icon="search" /></td></tr>
    @endforelse
    <x-slot:footer><x-ui.pagination :paginator="$lecturers" /></x-slot:footer>
</x-ui.table>

<style>
    #lecturer-table [data-edit-lecturer] { display: none; }
    #lecturer-table [data-edit-lecturer] { cursor: pointer; }
    #lecturer-table[data-lecturer-edit-mode="on"] [data-edit-lecturer] { display: inline-flex; }
    #lecturer-table:not([data-lecturer-edit-mode="on"]) th:last-child,
    #lecturer-table:not([data-lecturer-edit-mode="on"]) td:last-child { display: none; }
</style>
