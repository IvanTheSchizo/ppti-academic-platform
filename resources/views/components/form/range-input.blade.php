@props(['name', 'min' => null, 'max' => null, 'step' => '0.01'])

<div {{ $attributes->merge(['class' => 'flex h-control w-full items-center rounded-ui border border-border bg-surface px-3 transition-colors hover:border-muted focus-within:border-primary focus-within:outline-2 focus-within:outline-primary/30']) }}>
    <input type="number" step="{{ $step }}" name="{{ $name }}_min" value="{{ $min }}" placeholder="Min" aria-label="Minimum"
           class="w-full min-w-0 bg-transparent text-center text-body text-text placeholder:text-muted focus:outline-none">

    <span class="px-2 text-muted" aria-hidden="true">&ndash;</span>

    <input type="number" step="{{ $step }}" name="{{ $name }}_max" value="{{ $max }}" placeholder="Max" aria-label="Maximum"
           class="w-full min-w-0 bg-transparent text-center text-body text-text placeholder:text-muted focus:outline-none">
</div>
