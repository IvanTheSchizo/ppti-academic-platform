@props(['variant' => 'info', 'title' => null])

<div data-toast @unless ($variant === 'danger') data-toast-auto @endunless
     class="rounded-ui bg-surface shadow-md">
    <x-ui.alert :variant="$variant" :title="$title" class="relative pr-12">
        {{ $slot }}

        <button type="button" data-toast-close aria-label="Dismiss"
                class="absolute top-2 right-2 grid size-8 place-items-center rounded-ui text-muted transition-colors hover:bg-page hover:text-text active:bg-border/50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
            <x-ui.icon name="close" />
        </button>
    </x-ui.alert>
</div>
