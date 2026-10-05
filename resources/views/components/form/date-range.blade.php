@props(['name', 'from' => null, 'to' => null])

@php
    $input = 'min-w-0 flex-1 bg-transparent text-body text-text focus:outline-none [&::-webkit-calendar-picker-indicator]:hidden';
@endphp

<div {{ $attributes->merge(['class' => 'flex h-control w-full items-center gap-2 rounded-ui border border-border bg-surface px-3 transition-colors hover:border-muted focus-within:border-primary focus-within:outline-2 focus-within:outline-primary/30']) }}>
    <x-ui.icon name="date_range" class="size-5 shrink-0 text-muted" />

    <input type="date" name="{{ $name }}_from" value="{{ $from }}" aria-label="From date" class="{{ $input }}">

    <span class="text-muted" aria-hidden="true">&ndash;</span>

    <input type="date" name="{{ $name }}_to" value="{{ $to }}" aria-label="To date" class="{{ $input }}">
</div>
