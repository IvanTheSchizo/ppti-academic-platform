@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header title="Audit Log" divider>
            <x-slot:actions>
                <x-ui.button type="button" variant="accent" data-modal-open="#download-audit-logs-modal">
                    <x-ui.icon name="download" /> Download
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Filter Bar --}}
        @include('audit-logs.partials.filters')

        {{-- Audit Log Table --}}
        @include('audit-logs.partials.table')
    </div>

    <x-ui.download-modal
        id="download-audit-logs-modal"
        title="Download Audit Logs"
        :action="route('audit-logs.export')"
        :columns="['Timestamp', 'Admin', 'Action', 'Target Entity', 'Target ID']"
        search-placeholder="Search by admin, action, or entity"
        item-label="audit log"
    >
        @forelse ($exportAuditLogs as $log)
            @php
                $searchString = strtolower($log->timestamp . ' ' . $log->admin . ' ' . $log->action . ' ' . $log->target_entity . ' ' . $log->target_id);
            @endphp
            <tr data-download-row data-search="{{ $searchString }}" class="cursor-pointer transition-colors hover:bg-page">
                <td class="px-3 py-3 text-center">
                    <input type="checkbox" name="ids[]" value="{{ $log->id }}" data-download-cb class="size-4 shrink-0 cursor-pointer rounded border-border text-primary accent-navy focus:ring-primary">
                </td>
                <td class="whitespace-nowrap px-3 py-3 font-mono text-body">{{ $log->timestamp }}</td>
                <td class="px-3 py-3 font-medium text-text">{{ $log->admin }}</td>
                <td class="px-3 py-3">{{ ucfirst(strtolower($log->action)) }}</td>
                <td class="px-3 py-3">{{ $log->target_entity }}</td>
                <td class="px-3 py-3 font-mono text-body">{{ $log->target_id }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="p-6 text-center text-muted">No audit logs found to download.</td>
            </tr>
        @endforelse
    </x-ui.download-modal>
@endsection
