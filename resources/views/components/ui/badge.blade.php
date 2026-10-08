@props(['color' => 'muted', 'variant' => 'status'])

@php
    $dots = [
        'muted' => 'bg-muted',
        'success' => 'bg-success',
        'primary' => 'bg-primary',
        'danger' => 'bg-danger',
        'warning' => 'bg-warning',
    ];

    $neutral = $variant === 'neutral';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-caption text-text ' . ($neutral ? 'bg-subtle' : 'border border-border bg-surface shadow-sm')]) }}>
    @unless ($neutral)
        <span class="size-1.5 rounded-full {{ $dots[$color] ?? $dots['muted'] }}" aria-hidden="true"></span>
    @endunless
    {{ $slot }}
</span>
