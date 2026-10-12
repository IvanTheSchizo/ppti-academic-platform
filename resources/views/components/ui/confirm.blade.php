@props([
    'id',
    'title' => 'Are you sure?',
    'message' => 'Please confirm if you want to proceed.',
    'confirmLabel' => 'Confirm',
    'cancelLabel' => 'Cancel',
    'form' => null,
    'variant' => 'primary',
])

{{-- Open with data-modal-open="#{{ $id }}". Confirm submits the form with id $form,
     or, without one, closes the dialog and fires a "confirmed" event on it. --}}
<dialog id="{{ $id }}" aria-labelledby="{{ $id }}-title" aria-describedby="{{ $id }}-message"
        {{ $attributes->merge(['class' => 'm-auto w-[calc(100%-2rem)] max-w-xs overflow-visible rounded-ui border border-border bg-surface p-0 text-text backdrop:bg-black/60']) }}>
    <div class="relative flex flex-col items-center px-card pt-8 pb-card text-center">
        <button type="button" data-modal-close aria-label="Close"
                class="absolute top-3 right-3 grid size-8 place-items-center rounded-ui text-text transition-colors hover:bg-page active:bg-border/50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
            <x-ui.icon name="close" />
        </button>

        <x-ui.icon name="help" class="size-8 text-primary" />

        <h2 id="{{ $id }}-title" class="mt-3 text-section font-semibold">{{ $title }}</h2>
        <p id="{{ $id }}-message" class="mt-1 text-body text-muted">{{ $slot->isEmpty() ? $message : $slot }}</p>

        <div class="mt-6 flex justify-center gap-3">
            <x-ui.button variant="secondary" type="button" data-modal-close>{{ $cancelLabel }}</x-ui.button>

            @if ($form)
                <x-ui.button :variant="$variant" form="{{ $form }}" data-modal-close>{{ $confirmLabel }}</x-ui.button>
            @else
                <x-ui.button :variant="$variant" type="button" data-confirm-accept>{{ $confirmLabel }}</x-ui.button>
            @endif
        </div>
    </div>
</dialog>
