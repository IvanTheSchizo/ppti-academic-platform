@props(['label', 'value', 'color' => 'muted'])

@php
    $dots = [
        'muted' => 'bg-muted',
        'success' => 'bg-success',
        'primary' => 'bg-primary',
        'danger' => 'bg-danger',
        'warning' => 'bg-warning',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-2 text-base']) }}>
    <span class="size-2.5 rounded-full {{ $dots[$color] ?? $dots['muted'] }}" aria-hidden="true"></span>
    <span class="text-muted">{{ $label }}</span>
    <span class="font-medium text-text">{{ $value }}</span>
</div>
