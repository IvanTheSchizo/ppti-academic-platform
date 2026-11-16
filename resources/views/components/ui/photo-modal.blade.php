@props([
    'id' => 'edit-photo-modal',
    'title' => 'Profile Photo',
    'src' => null,
    'alt' => '',
    'uploadAction',
    'deleteAction',
])

<x-ui.modal :id="$id" :title="$title" size="sm">
    <div data-photo-modal class="space-y-5">
        <div class="flex flex-col items-center gap-3">
            <div class="relative inline-flex size-28 items-center justify-center overflow-hidden rounded-full border-2 border-text bg-page">
                <img src="{{ $src ?? '' }}" alt="{{ $alt }}" data-photo-preview class="size-full object-cover {{ $src ? '' : 'hidden' }}">
                <span data-photo-placeholder class="flex size-full items-center justify-center {{ $src ? 'hidden' : '' }}">
                    <x-ui.icon name="person" class="size-14 text-text" />
                </span>
            </div>
            <p class="text-center text-caption text-muted">JPG, PNG, or WebP (max 5 MB). Automatically resized and compressed.</p>
        </div>

        <form method="POST" action="{{ $uploadAction }}" enctype="multipart/form-data" id="{{ $id }}-upload-form" class="space-y-4">
            @csrf
            <label class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-ui border border-dashed border-border px-4 py-4 text-center transition-colors hover:border-muted focus-within:border-primary">
                <span class="text-body text-text"><span class="text-link underline">Choose a photo</span></span>
                <span data-photo-filename class="text-caption text-muted" hidden></span>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required data-photo-input class="sr-only">
            </label>
        </form>

        @if ($src)
            <form method="POST" action="{{ $deleteAction }}" id="{{ $id }}-delete-form" hidden>
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>

    <x-slot:footer>
        @if ($src)
            <x-ui.button type="submit" form="{{ $id }}-delete-form" variant="danger" class="mr-auto">
                <x-ui.icon name="delete" /> Remove
            </x-ui.button>
        @endif
        <x-ui.button type="button" variant="secondary" data-modal-close>Cancel</x-ui.button>
        <x-ui.button type="submit" form="{{ $id }}-upload-form" data-photo-submit disabled>Save Photo</x-ui.button>
    </x-slot:footer>
</x-ui.modal>
