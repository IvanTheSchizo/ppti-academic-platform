{{-- Import CSV modal for one Input Data tab. Behaviour lives in app.js (initCsvImport). Expects $tab. --}}
@php
    $columns = \App\Services\InputDataImporter::COLUMNS[$tab];
@endphp

<x-ui.modal id="import-modal" title="Import CSV Data" size="lg">
    <div data-csv-import
         data-preview-url="{{ route('input-data.import.preview', $tab) }}"
         data-commit-url="{{ route('input-data.import.commit', $tab) }}">

        {{-- Step 1: choose a file --}}
        <div data-import-step="upload" class="space-y-4">
            <label data-import-dropzone class="flex min-h-72 cursor-pointer flex-col items-center justify-center gap-3 rounded-ui border border-dashed border-border px-4 text-center transition-colors hover:border-muted focus-within:border-primary data-[dragging]:border-primary data-[dragging]:bg-primary/5">
                <span class="grid size-14 place-items-center rounded-ui bg-primary/10 text-navy">
                    <x-ui.icon name="cloud_upload" class="size-7" />
                </span>
                <span class="text-base text-text">Drag &amp; drop your CSV file here, or <span class="text-link underline">browse files</span></span>
                <span class="text-body text-muted">Supported file types: .CSV &bull; Maximum file size: 10MB</span>
                <span data-import-filename class="text-body font-semibold text-text" hidden></span>
                <input type="file" accept=".csv,text/csv" data-import-file class="sr-only">
            </label>

            <p class="text-body text-muted">
                Columns: {{ implode(', ', array_map(fn ($column, $required) => $column . ($required ? '*' : ''), array_keys($columns), $columns)) }}
                (* required).
                <a href="{{ route('input-data.import.template', $tab) }}" class="text-link hover:underline">Download template</a>
            </p>
            @if ($tab === 'student')
                <p class="text-body text-muted">For each student, include both <strong>batch</strong> and <strong>class_code</strong>. Missing batches and classes will be created automatically when you confirm the import; existing ones will be reused.</p>
            @elseif ($tab === 'class')
                <p class="text-body text-muted">Missing batches will be created automatically when you confirm the import. Existing batches will be reused.</p>
            @endif
        </div>

        {{-- Step 2: review the parsed rows --}}
        <div data-import-step="preview" class="space-y-4" hidden>
            <p data-import-summary class="text-body text-text"></p>
            <div class="max-h-96 overflow-auto rounded-ui border border-border">
                <table class="w-full border-collapse text-left text-body">
                    <thead data-import-head class="sticky top-0 bg-subtle text-caption uppercase tracking-wide"></thead>
                    <tbody data-import-body class="[&>tr]:border-t [&>tr]:border-border"></tbody>
                </table>
            </div>
        </div>

        <x-ui.alert variant="danger" class="mt-4" data-import-error hidden></x-ui.alert>
    </div>

    <x-slot:footer>
        <x-ui.button type="button" variant="secondary" data-modal-close>Cancel</x-ui.button>
        <x-ui.button type="button" variant="secondary" data-import-back hidden>Back</x-ui.button>
        <x-ui.button type="button" data-import-upload>
            <x-ui.icon name="cloud_upload" /> Upload &amp; Preview
        </x-ui.button>
        <x-ui.button type="button" data-import-commit hidden>Import</x-ui.button>
    </x-slot:footer>
</x-ui.modal>
