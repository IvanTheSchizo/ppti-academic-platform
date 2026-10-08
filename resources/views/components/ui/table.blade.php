@props(['headings' => [], 'sort' => null, 'direction' => null])

@php
    $sort = $sort ?? request()->query('sort');
    $direction = $direction ?? (request()->query('direction') === 'desc' ? 'desc' : 'asc');
@endphp

<div class="overflow-hidden rounded-card border border-border bg-surface">
    <div class="overflow-x-auto">
        <table {{ $attributes->merge(['class' => 'w-full border-collapse text-left']) }}>
            <thead>
                <tr>
                    @foreach ($headings as $heading)
                        @php
                            $column = is_array($heading) ? $heading : ['label' => $heading];
                            $label = $column['label'];
                            $key = $column['key'] ?? \Illuminate\Support\Str::slug($label, '_');
                            $sortable = $column['sortable'] ?? true;
                            $active = $sortable && $sort === $key;

                            $href = match (true) {
                                ! $sortable => null,
                                $active && $direction === 'desc' => request()->fullUrlWithoutQuery(['sort', 'direction', 'page']),
                                default => request()->fullUrlWithQuery(['sort' => $key, 'direction' => $active ? 'desc' : 'asc', 'page' => 1]),
                            };
                        @endphp

                        <th scope="col"
                            @if ($sortable) aria-sort="{{ $active ? ($direction === 'desc' ? 'descending' : 'ascending') : 'none' }}" @endif
                            class="border-r border-b border-border bg-subtle p-0 text-body font-bold uppercase tracking-wide last:border-r-0">
                            @if ($sortable)
                                <a href="{{ $href }}"
                                   class="flex h-10 items-center justify-between gap-2 px-4 transition-colors hover:bg-border/40 active:bg-border/70 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-primary">
                                    {{ $label }}

                                    <span class="relative size-5 shrink-0" aria-hidden="true">
                                        <x-ui.icon name="arrow_drop_up" class="absolute inset-0 size-5 -translate-y-[3px] {{ $active && $direction === 'asc' ? 'text-text' : 'text-muted/50' }}" />
                                        <x-ui.icon name="arrow_drop_down" class="absolute inset-0 size-5 translate-y-[3px] {{ $active && $direction === 'desc' ? 'text-text' : 'text-muted/50' }}" />
                                    </span>
                                </a>
                            @else
                                <span class="flex h-10 items-center px-4">{{ $label }}</span>
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="[&>tr:hover]:bg-page [&>tr:last-child>td]:border-b-0 [&_td]:h-12.5 [&_td]:border-r [&_td]:border-b [&_td]:border-border [&_td]:px-4 [&_td:last-child]:border-r-0">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @isset($footer)
        <div class="border-t-2 border-border px-4 py-3">{{ $footer }}</div>
    @endisset
</div>
