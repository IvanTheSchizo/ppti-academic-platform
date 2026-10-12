@props(['title' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'rounded-card border border-border bg-surface p-card']) }}>
    @if ($title)
        <div class="mb-6 flex items-center justify-between gap-4 border-b border-border pb-4">
            <h2 class="flex items-center gap-3 text-section text-text">
                @if ($icon)
                    <x-ui.icon :name="$icon" class="size-6 text-navy" />
                @endif
                {{ $title }}
            </h2>

            @isset($actions)
                <div class="text-body">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    {{ $slot }}
</div>
