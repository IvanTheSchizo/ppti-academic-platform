@props(['disabled' => false, 'size' => 'md', 'filled' => false])

@php
    $sizes = [
        'md' => 'h-control text-body',
        'lg' => 'h-11 text-base',
    ];

    $name = $attributes->get('name');
    $id = $attributes->get('id', $name);
    $invalid = isset($errors) && $name && $errors->has($name);

    $state = $invalid
        ? 'border-danger focus:outline-danger/30'
        : 'border-border enabled:hover:border-muted focus:border-primary focus:outline-primary/30';
@endphp

<input @disabled($disabled)
       @if ($invalid) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
       {{ $attributes->merge(['id' => $id, 'class' => ($sizes[$size] ?? $sizes['md']) . ' ' . ($filled ? 'bg-page' : 'bg-surface') . " w-full rounded-ui border px-3 text-text placeholder:text-muted focus:outline-2 disabled:cursor-not-allowed disabled:bg-page disabled:text-muted {$state}"]) }}>
