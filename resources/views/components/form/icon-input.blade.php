@props(['icon'])

<div {{ $attributes->only('class')->merge(['class' => 'relative']) }}>
    <x-ui.icon :name="$icon" class="pointer-events-none absolute top-1/2 left-3 size-5 -translate-y-1/2 text-muted" />

    <x-form.text-input :attributes="$attributes->except('class')->merge(['class' => 'pl-10'])" />
</div>
