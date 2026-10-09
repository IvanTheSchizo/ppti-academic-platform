<x-ui.table :headings="[
    ['label' => 'Timestamp', 'key' => 'timestamp'],
    ['label' => 'Admin', 'sortable' => false],
    ['label' => 'Action', 'sortable' => false],
    ['label' => 'Target Entity', 'sortable' => false],
    ['label' => 'Target ID', 'sortable' => false],
    ['label' => 'Old Value', 'sortable' => false],
    ['label' => 'New Value', 'sortable' => false],
]">
    @forelse ($auditLogs as $log)
        <tr>
            <td class="whitespace-nowrap py-3 font-mono text-body">{{ $log->timestamp }}</td>
            <td class="py-3">{{ $log->admin }}</td>
            <td class="py-3">{{ ucfirst(strtolower($log->action)) }}</td>
            <td class="py-3">{{ $log->target_entity }}</td>
            <td class="py-3 font-mono text-body">{{ $log->target_id }}</td>
            <td class="py-3 font-mono text-body text-muted">@forelse ($log->old_value as $line)<div>{{ $line }}</div>@empty<span class="text-muted" aria-label="None">&mdash;</span>@endforelse</td>
            <td class="py-3 font-mono text-body">@forelse ($log->new_value as $line)<div>{{ $line }}</div>@empty<span class="text-muted" aria-label="None">&mdash;</span>@endforelse</td>
        </tr>
    @empty
        <tr>
            <td colspan="7">
                <x-ui.empty-state title="No audit logs found" description="There are no system logs matching your filters." icon="search" />
            </td>
        </tr>
    @endforelse

    <x-slot:footer>
        <x-ui.pagination :paginator="$auditLogs" />
    </x-slot:footer>
</x-ui.table>
