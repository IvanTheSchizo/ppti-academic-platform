@props(['size' => 'md'])

@php
    $sizes = [
        'sm' => 'h-8 pl-2 pr-8',
        'md' => 'h-control w-full pl-3 pr-10',
    ];

    $name = $attributes->get('name');
    $id = $attributes->get('id', $name);
    $invalid = isset($errors) && $name && $errors->has($name);

    $state = $invalid
        ? 'border-danger focus:outline-danger/30'
        : 'border-border enabled:hover:border-muted focus:border-primary focus:outline-primary/30';
@endphp

<div class="relative {{ $size === 'sm' ? 'inline-block' : 'block' }}">
    <select @if ($invalid) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->merge(['id' => $id, 'class' => 'appearance-none rounded-ui border bg-surface text-body text-text focus:outline-2 disabled:cursor-not-allowed disabled:bg-page disabled:text-muted ' . $state . ' ' . ($sizes[$size] ?? $sizes['md'])]) }}>
        {{ $slot }}
    </select>

    <x-ui.icon name="keyboard_arrow_down" class="pointer-events-none absolute top-1/2 right-2 size-5 -translate-y-1/2 text-text" />
</div>
