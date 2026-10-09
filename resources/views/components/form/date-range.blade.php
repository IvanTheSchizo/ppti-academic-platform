@props(['name', 'from' => null, 'to' => null, 'placeholder' => 'Select date range'])

@php
    $input = 'min-w-0 flex-1 bg-transparent text-body text-text focus:outline-none [&::-webkit-calendar-picker-indicator]:hidden';
    $empty = '[&[data-empty]:not(:focus-within)_input]:text-transparent [&[data-empty]:not(:focus-within)_[data-sep]]:invisible';
@endphp

{{-- The browser's native "dd/mm/yyyy" hint is hidden while both dates are empty and unfocused; data-empty is kept in sync in app.js. --}}
<div data-date-range @if (blank($from) && blank($to)) data-empty @endif
     {{ $attributes->merge(['class' => "relative flex h-control w-full items-center gap-2 rounded-ui border border-border bg-surface px-3 transition-colors hover:border-muted focus-within:border-primary focus-within:outline-2 focus-within:outline-primary/30 {$empty}"]) }}>
    <x-ui.icon name="date_range" class="size-5 shrink-0 text-muted" />

    <span class="pointer-events-none absolute left-10 hidden text-body text-muted [[data-empty]:not(:focus-within)>&]:block">{{ $placeholder }}</span>

    <input type="date" name="{{ $name }}_from" value="{{ $from }}" aria-label="From date" class="{{ $input }}">

    <span data-sep class="text-muted" aria-hidden="true">&ndash;</span>

    <input type="date" name="{{ $name }}_to" value="{{ $to }}" aria-label="To date" class="{{ $input }}">
</div>
