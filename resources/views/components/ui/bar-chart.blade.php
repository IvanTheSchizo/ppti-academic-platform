@props(['items' => [], 'max' => 4])

<div {{ $attributes->merge(['class' => 'space-y-3.5']) }} role="list">
    @foreach ($items as $item)
        <div role="listitem" class="flex items-center gap-4">
            <span class="w-24 shrink-0 text-body text-muted">{{ $item['label'] }}</span>

            <div class="flex-1">
                <div class="flex h-7 items-center justify-end bg-chart px-2 text-body font-semibold text-on-primary"
                     style="width: {{ min(100, max(0, $item['value'] / $max * 100)) }}%">
                    {{ number_format($item['value'], 2) }}
                </div>
            </div>
        </div>
    @endforeach
</div>
