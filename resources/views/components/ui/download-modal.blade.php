@props([
    'id' => 'download-modal',
    'title' => 'Download CSV',
    'action',
    'columns' => [],
    'searchPlaceholder' => 'Search...',
    'itemLabel' => 'record',
    'params' => [],
])

<x-ui.modal :id="$id" :title="$title" size="lg">
    <form method="GET" action="{{ $action }}" class="space-y-4" data-download-modal data-item-label="{{ $itemLabel }}">
        @foreach ($params as $name => $value)
            @if (! is_array($value) && filled($value))
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endif
        @endforeach

        <div class="flex flex-wrap items-center justify-between gap-4">
            <x-form.search-input data-download-search :placeholder="$searchPlaceholder" class="w-full max-w-sm" />

            <div class="flex items-center gap-2">
                <x-ui.button type="button" variant="secondary" size="sm" data-download-select-all-btn>
                    Select all
                </x-ui.button>
                <x-ui.button type="button" variant="secondary" size="sm" data-download-deselect-all-btn>
                    Deselect all
                </x-ui.button>
            </div>
        </div>

        <div class="max-h-96 overflow-y-auto rounded-ui border border-border shadow-sm">
            <table class="w-full border-collapse text-left text-body">
                <thead class="sticky top-0 z-10 bg-surface shadow-sm outline outline-1 outline-border">
                    <tr class="text-caption uppercase tracking-wide text-muted">
                        <th class="w-10 px-3 py-3 font-normal text-center">
                            <input type="checkbox" data-download-select-all aria-label="Select all" class="size-4 shrink-0 cursor-pointer rounded border-border text-primary accent-navy focus:ring-primary">
                        </th>
                        @foreach ($columns as $column)
                            <th class="px-3 py-3 font-normal">{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-border" data-download-tbody>
                    {{ $slot }}
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between border-t border-border pt-4">
            <span class="text-sm font-medium text-muted"><span data-download-count>0</span> selected</span>
            <div class="flex gap-3">
                <x-ui.button type="button" variant="secondary" data-modal-close>Cancel</x-ui.button>
                <x-ui.button type="submit" data-download-submit>
                    <x-ui.icon name="download" /> <span data-download-submit-label>Download 0 {{ $itemLabel }}s</span>
                </x-ui.button>
            </div>
        </div>
    </form>
</x-ui.modal>
