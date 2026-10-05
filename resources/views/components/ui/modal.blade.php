@props(['id', 'title', 'size' => 'md'])

@php
    $sizes = ['sm' => 'max-w-sm', 'md' => 'max-w-lg', 'lg' => 'max-w-3xl'];
@endphp

<dialog id="{{ $id }}" aria-labelledby="{{ $id }}-title"
        {{ $attributes->merge(['class' => 'm-auto max-h-[calc(100vh-2rem)] w-[calc(100%-2rem)] overflow-y-auto rounded-ui border border-border bg-surface p-0 text-text backdrop:bg-black/50 ' . ($sizes[$size] ?? $sizes['md'])]) }}>
    <div class="flex items-center justify-between border-b border-border px-card py-4">
        <h2 id="{{ $id }}-title" class="text-section">{{ $title }}</h2>

        <button type="button" data-modal-close aria-label="Close"
                class="grid size-8 place-items-center rounded-ui text-muted transition-colors hover:bg-page hover:text-text active:bg-border/50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
            <x-ui.icon name="close" />
        </button>
    </div>

    <div class="p-card">{{ $slot }}</div>

    @isset($footer)
        <div class="flex justify-end gap-3 border-t border-border px-card py-4">{{ $footer }}</div>
    @endisset
</dialog>
