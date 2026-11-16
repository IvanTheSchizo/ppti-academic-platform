@props([
    'id' => 'bulk-photo-modal',
    'title' => 'Bulk Upload Photos',
    'previewUrl',
    'commitUrl',
    'exampleFilename' => '2700000001.jpg',
    'matchLabel' => 'Student ID',
])

<x-ui.modal :id="$id" :title="$title" size="lg">
    <div data-bulk-photo
         data-preview-url="{{ $previewUrl }}"
         data-commit-url="{{ $commitUrl }}">

        {{-- Step 1: choose a ZIP file --}}
        <div data-bulk-step="upload" class="space-y-4">
            <label data-bulk-dropzone class="flex min-h-64 cursor-pointer flex-col items-center justify-center gap-3 rounded-ui border border-dashed border-border px-4 text-center transition-colors hover:border-muted focus-within:border-primary data-[dragging]:border-primary data-[dragging]:bg-primary/5">
                <span class="grid size-14 place-items-center rounded-ui bg-primary/10 text-navy">
                    <x-ui.icon name="cloud_upload" class="size-7" />
                </span>
                <span class="text-base text-text">Drag &amp; drop your ZIP file here, or <span class="text-link underline">browse files</span></span>
                <span class="text-body text-muted">Supported file types: .ZIP containing JPG, PNG, or WebP images &bull; Max ZIP size: 50MB</span>
                <span data-bulk-filename class="text-body font-semibold text-text" hidden></span>
                <input type="file" accept=".zip,application/zip" data-bulk-file class="sr-only">
            </label>

            <p class="text-body text-muted">
                Name each image after the {{ $matchLabel }} (for example, <strong>{{ $exampleFilename }}</strong>). Subfolders inside the ZIP are supported, and existing photos with a matching {{ $matchLabel }} will be replaced.
            </p>
        </div>

        {{-- Step 2: review matched files --}}
        <div data-bulk-step="preview" class="space-y-4" hidden>
            <p data-bulk-summary class="text-body text-text"></p>
            <div class="max-h-96 overflow-auto rounded-ui border border-border">
                <table class="w-full border-collapse text-left text-body">
                    <thead class="sticky top-0 bg-subtle text-caption uppercase tracking-wide">
                        <tr>
                            <th class="px-3 py-2 font-semibold">Filename</th>
                            <th class="px-3 py-2 font-semibold">{{ $matchLabel }}</th>
                            <th class="px-3 py-2 font-semibold">Matched To</th>
                            <th class="px-3 py-2 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody data-bulk-body class="[&>tr]:border-t [&>tr]:border-border"></tbody>
                </table>
            </div>
        </div>

        <x-ui.alert variant="danger" class="mt-4" data-bulk-error hidden></x-ui.alert>
    </div>

    <x-slot:footer>
        <x-ui.button type="button" variant="secondary" data-modal-close>Cancel</x-ui.button>
        <x-ui.button type="button" variant="secondary" data-bulk-back hidden>Back</x-ui.button>
        <x-ui.button type="button" data-bulk-upload>
            <x-ui.icon name="cloud_upload" /> Upload &amp; Preview
        </x-ui.button>
        <x-ui.button type="button" data-bulk-commit hidden>Upload Photos</x-ui.button>
    </x-slot:footer>
</x-ui.modal>
