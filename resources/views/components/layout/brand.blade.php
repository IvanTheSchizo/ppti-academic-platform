@props(['href' => null, 'compact' => false])

@php
    $logo = file_exists(public_path('svg/binus-logo.svg')) ? asset('svg/binus-logo.svg') : null;
    $tag = $href ? 'a' : 'div';
    $link = $href ? ' rounded-ui transition-opacity hover:opacity-80 active:opacity-60 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary' : '';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'flex min-w-0 items-center gap-3 sm:gap-4' . $link]) }}>
    @if ($logo)
        <img src="{{ $logo }}" alt="Binus University" class="{{ $compact ? 'hidden sm:block' : '' }} h-10 shrink-0">
        <span class="{{ $compact ? 'hidden sm:block' : '' }} h-12 w-px shrink-0 bg-border" aria-hidden="true"></span>
    @endif

    <span class="truncate uppercase {{ $compact ? 'text-body sm:text-base' : 'text-base' }}">PPTI Academic Platform</span>
</{{ $tag }}>
