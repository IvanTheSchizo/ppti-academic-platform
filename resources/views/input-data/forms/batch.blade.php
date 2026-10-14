<div class="max-w-xl">
    <x-form.field name="batch_name" label="Batch Name" :required="true">
        <x-form.text-input name="batch_name" :value="old('batch_name')" placeholder="e.g. PPTI 23" maxlength="255" autocomplete="off" autofocus />
    </x-form.field>
    <p class="mt-2 text-body text-muted">Create the batch first. You can then add classes that belong to this batch.</p>
</div>
