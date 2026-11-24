@props([
    'name',
    'options' => [],
    'selected' => [],
    'placeholder' => 'Select options',
    'searchPlaceholder' => 'Filter...',
    'emptyMessage' => 'No options available.',
])

@php
    $selectedStrings = array_map('strval', $selected);
@endphp

<div data-multi-select>
    <details data-dropdown class="relative">
        <summary class="flex min-h-control w-full cursor-pointer list-none items-center justify-between gap-2 rounded-ui border border-border bg-surface py-1.5 pl-3 pr-2 text-body transition-colors hover:border-muted focus-visible:border-primary focus-visible:outline-2 focus-visible:outline-primary/30 [&::-webkit-details-marker]:hidden">
            <div class="flex min-w-0 flex-1 flex-wrap items-center gap-1.5">
                <span data-ms-placeholder class="truncate text-muted">{{ $placeholder }}</span>
                <div data-ms-pills class="hidden flex-wrap items-center gap-1.5"></div>
            </div>
            <x-ui.icon name="keyboard_arrow_down" class="size-5 shrink-0 text-text" />
        </summary>

        <div class="absolute inset-x-0 top-full z-20 mt-1 overflow-hidden rounded-ui border border-border bg-surface shadow-lg">
            @if (empty($options))
                <p class="px-3 py-3 text-body text-muted">{{ $emptyMessage }}</p>
            @else
                <div class="border-b border-border p-2">
                    <input type="search"
                           data-ms-search
                           placeholder="{{ $searchPlaceholder }}"
                           autocomplete="off"
                           class="h-8 w-full rounded-ui border border-border bg-page px-2.5 text-body text-text placeholder:text-muted focus:border-primary focus:bg-surface focus:outline-2 focus:outline-primary/30">
                </div>

                <div class="max-h-48 overflow-y-auto p-1.5">
                    @foreach ($options as $option)
                        <label data-ms-option
                               data-search="{{ strtolower((string) $option['label']) }}"
                               class="flex cursor-pointer items-center gap-2.5 rounded-ui px-2.5 py-1.5 text-body text-text transition-colors hover:bg-page">
                            <input type="checkbox"
                                   name="{{ $name }}"
                                   value="{{ $option['value'] }}"
                                   data-ms-checkbox
                                   data-label="{{ $option['label'] }}"
                                   @checked(in_array((string) $option['value'], $selectedStrings, true))
                                   class="size-4 shrink-0 cursor-pointer rounded border-border text-primary accent-navy focus:ring-primary">
                            <span class="truncate">{{ $option['label'] }}</span>
                        </label>
                    @endforeach

                    <p data-ms-no-match hidden class="px-2.5 py-3 text-center text-caption text-muted">No matching options.</p>
                </div>

                <div class="flex items-center justify-between border-t border-border bg-page/60 px-2.5 py-1.5 text-caption">
                    <button type="button" data-ms-clear class="cursor-pointer font-medium text-muted hover:text-text">Clear all</button>
                    <button type="button" data-ms-done class="cursor-pointer font-medium text-link hover:underline">Done</button>
                </div>
            @endif
        </div>
    </details>
</div>
