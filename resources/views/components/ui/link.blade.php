@props(['href' => '#'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'rounded-ui text-link transition-colors hover:text-link-hover hover:underline active:text-navy-active focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary']) }}>{{ $slot }}</a>
