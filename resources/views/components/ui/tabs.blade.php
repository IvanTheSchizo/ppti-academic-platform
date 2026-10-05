@props(['tabs' => []])

<nav aria-label="Tabs" {{ $attributes->merge(['class' => 'flex gap-6 border-b border-border']) }}>
    @foreach ($tabs as $tab)
        @php $active = ! empty($tab['active']); @endphp
        <a href="{{ $tab['href'] ?? '#' }}"
           @if ($active) aria-current="page" @endif
           class="-mb-px border-b-2 px-2 py-3 text-body transition-colors focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-primary
                  {{ $active ? 'border-accent font-semibold text-accent-text' : 'border-transparent text-text hover:border-border hover:text-navy active:border-muted active:text-navy-active' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
