@props(['icon' => true])

<div {{ $attributes->only('class')->merge(['class' => 'relative']) }}>
    @if ($icon)
        <x-ui.icon name="lock" class="pointer-events-none absolute top-1/2 left-3 size-5 -translate-y-1/2 text-muted" />
    @endif

    <x-form.text-input type="password" :attributes="$attributes->except('class')->merge(['class' => ($icon ? 'pl-10 ' : '') . 'pr-11'])" />

    <button type="button" data-password-toggle aria-label="Show password" aria-pressed="false"
            class="absolute top-1/2 right-1 grid size-8 -translate-y-1/2 place-items-center rounded-ui text-muted transition-colors hover:text-text active:bg-border/50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
        <x-ui.icon name="visibility_off" class="size-5" data-icon-hidden />
        <x-ui.icon name="visibility" class="hidden size-5" data-icon-shown />
    </button>
</div>
