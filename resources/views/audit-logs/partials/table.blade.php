<x-ui.table :headings="[
    ['label' => 'Timestamp', 'key' => 'timestamp'],
    'Admin',
    'Action',
    'Target Entity',
    'Target ID',
    'Old Value',
    'New Value',
]">
    @forelse ($auditLogs as $log)
        <tr class="h-14">
            <td class="whitespace-nowrap px-4 py-3">{{ $log->timestamp }}</td>
            <td class="px-4 py-3 font-medium">{{ $log->admin }}</td>
            <td class="px-4 py-3">
                {{-- Capsule button semi-transparent tanpa border dengan soft shadow --}}
                <span class="inline-flex items-center justify-center px-5 py-1.5 rounded-full text-xs font-medium bg-black/[0.04] text-text shadow-sm border-0">
                    {{ $log->action }}
                </span>
            </td>
            <td class="px-4 py-3"><span class="text-primary font-medium">{{ $log->target_entity }}</span></td>
            <td class="px-4 py-3">{{ $log->target_id }}</td>
            <td class="px-4 py-3 text-muted text-sm">{{ $log->old_value }}</td>
            <td class="px-4 py-3 text-sm">{{ $log->new_value }}</td>
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