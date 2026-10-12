@props(['tabs' => [], 'variant' => 'accent'])

{{-- Each tab: label, href, active, optional icon. "navy" variant is the icon tab bar used by Input Data. --}}
@php
    $activeClasses = $variant === 'navy'
        ? 'border-navy font-semibold text-navy'
        : 'border-accent font-semibold text-accent-text';
@endphp

<nav aria-label="Tabs" {{ $attributes->merge(['class' => 'flex gap-6 overflow-x-auto border-b border-border']) }}>
    @foreach ($tabs as $tab)
        @php $active = ! empty($tab['active']); @endphp
        <a href="{{ $tab['href'] ?? '#' }}"
           @if ($active) aria-current="page" @endif
           class="-mb-px inline-flex shrink-0 items-center gap-2 border-b-2 px-2 py-3 text-body transition-colors focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-primary
                  {{ $active ? $activeClasses : 'border-transparent text-text hover:border-border hover:text-navy active:border-muted active:text-navy-active' }}">
            @if (! empty($tab['icon']))
                <x-ui.icon :name="$tab['icon']" />
            @endif
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
