@props(['title', 'description' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center gap-2 px-6 py-12 text-center']) }}>
    @if ($icon)
        <x-ui.icon :name="$icon" class="size-10 text-muted" />
    @endif

    <p class="text-section text-text">{{ $title }}</p>

    @if ($description)
        <p class="text-body text-muted">{{ $description }}</p>
    @endif

    @unless ($slot->isEmpty())
        <div class="mt-2">{{ $slot }}</div>
    @endunless
</div>
