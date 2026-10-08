@props(['src' => null, 'alt' => '', 'size' => 'lg'])

@php
    $sizes = ['md' => 'size-10', 'lg' => 'size-16'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-text ' . ($sizes[$size] ?? $sizes['lg'])]) }}>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $alt }}" class="size-full object-cover">
    @else
        <x-ui.icon name="person" class="size-1/2 text-text" />
    @endif
</span>
