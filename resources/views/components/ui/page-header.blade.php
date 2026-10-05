@props(['title', 'subtitle' => null, 'divider' => false])

<div>
    <div {{ $attributes->merge(['class' => 'flex flex-wrap items-center justify-between gap-4']) }}>
        <div class="flex items-center gap-4">
            @isset($media)
                {{ $media }}
            @endisset

            <div>
                <h1 class="text-title text-text">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="text-base text-muted">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        @isset($actions)
            <div class="flex items-center gap-3">{{ $actions }}</div>
        @endisset
    </div>

    @if ($divider)
        <div class="mt-6 border-t-[3px] border-border"></div>
    @endif
</div>
