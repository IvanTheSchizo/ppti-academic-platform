@props(['items' => []])

@php
    $back = collect($items)->reverse()->first(fn ($item) => ! empty($item['href']));
@endphp

<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'flex items-center gap-3 text-body']) }}>
    @if ($back)
        <a href="{{ $back['href'] }}" aria-label="Back" class="rounded-ui text-muted transition-colors hover:text-text active:text-navy-active focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
            <x-ui.icon name="chevron_left" />
        </a>
    @endif

    <ol class="flex items-center gap-2">
        @foreach ($items as $item)
            <li class="flex items-center gap-2">
                @if (! $loop->last && ! empty($item['href']))
                    <a href="{{ $item['href'] }}" class="text-muted transition-colors hover:text-text hover:underline active:text-navy-active">{{ $item['label'] }}</a>
                    <span class="text-muted" aria-hidden="true">/</span>
                @else
                    <span @if ($loop->last) aria-current="page" @endif class="{{ $loop->last ? 'text-text' : 'text-muted' }}">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
