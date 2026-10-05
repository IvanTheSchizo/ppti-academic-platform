@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'submit',
    'href' => null,
    'disabled' => false,
    'loading' => false,
])

@php
    $variants = [
        'primary' => 'bg-navy text-on-primary hover:bg-navy-hover active:bg-navy-active',
        'secondary' => 'border border-border bg-surface text-text hover:bg-page active:bg-border/50',
        'danger' => 'bg-danger text-on-primary hover:bg-danger-hover active:bg-danger-active',
        'ghost' => 'text-text hover:bg-page active:bg-border/50',
    ];

    $sizes = [
        'sm' => 'h-8 px-3 text-body',
        'md' => 'h-control px-4 text-body',
        'lg' => 'h-12 px-6 text-base',
    ];

    $inactive = $disabled || $loading;

    $classes = 'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-ui font-medium transition-colors cursor-pointer '
        . 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary '
        . 'disabled:cursor-not-allowed disabled:opacity-50 '
        . ($variants[$variant] ?? $variants['primary']) . ' '
        . ($sizes[$size] ?? $sizes['md'])
        . ($inactive ? ' pointer-events-none opacity-50' : '');
@endphp

@if ($href)
    <a href="{{ $href }}" @if ($inactive) aria-disabled="true" tabindex="-1" @endif @if ($loading) aria-busy="true" @endif {{ $attributes->merge(['class' => $classes]) }}>
        @if ($loading)
            <span class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" @disabled($inactive) @if ($loading) aria-busy="true" @endif {{ $attributes->merge(['class' => $classes]) }}>
        @if ($loading)
            <span class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
        @endif
        {{ $slot }}
    </button>
@endif
