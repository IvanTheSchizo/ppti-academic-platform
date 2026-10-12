@props(['for' => null, 'caps' => true])

<label @if ($for) for="{{ $for }}" @endif {{ $attributes->merge(['class' => $caps ? 'mb-1 block text-caption uppercase tracking-wide text-muted' : 'mb-1 block text-body text-text']) }}>{{ $slot }}</label>
