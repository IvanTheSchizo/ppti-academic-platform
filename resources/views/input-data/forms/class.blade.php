<div class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
    <x-form.field name="batch_id" label="Batch" :required="true">
        <x-form.select name="batch_id">
            <option value="">Select Batch</option>
            @foreach ($batches as $batch)
                <option value="{{ $batch->id }}" @selected((string) old('batch_id') === (string) $batch->id)>{{ $batch->batch_name }}</option>
            @endforeach
        </x-form.select>
    </x-form.field>

    <x-form.field name="class_code" label="Class" :required="true">
        <x-form.text-input name="class_code" :value="old('class_code')" placeholder="e.g. 1A" maxlength="20" autocomplete="off" />
    </x-form.field>
</div>
