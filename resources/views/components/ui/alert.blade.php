@props(['variant' => 'info', 'title' => null])

@php
    $variants = [
        'success' => ['border-l-success bg-success/10', 'status'],
        'danger' => ['border-l-danger bg-danger/10', 'alert'],
        'warning' => ['border-l-warning bg-warning/10', 'alert'],
        'info' => ['border-l-primary bg-primary/10', 'status'],
    ];

    [$classes, $role] = $variants[$variant] ?? $variants['info'];
@endphp

<div role="{{ $role }}" {{ $attributes->merge(['class' => "rounded-ui border border-l-4 border-border px-4 py-3 text-body text-text {$classes}"]) }}>
    @if ($title)
        <p class="font-semibold">{{ $title }}</p>
    @endif

    <div>{{ $slot }}</div>
</div>
