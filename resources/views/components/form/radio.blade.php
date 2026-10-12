<label class="inline-flex cursor-pointer items-center gap-2 text-body text-text has-disabled:cursor-not-allowed has-disabled:opacity-50">
    <input type="radio" {{ $attributes->merge(['class' => 'size-4 shrink-0 cursor-pointer accent-navy focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</label>
