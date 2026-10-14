<div class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
    <x-form.field name="period_name" label="Period Name" :required="true">
        <x-form.text-input name="period_name" :value="old('period_name')" placeholder="e.g. Semester 1 - 2027" maxlength="255" autocomplete="off" autofocus />
    </x-form.field>

    <x-form.field name="start_date" label="Start Date" :required="true">
        <x-form.text-input type="date" name="start_date" :value="old('start_date')" />
    </x-form.field>

    <x-form.field name="end_date" label="End Date">
        <x-form.text-input type="date" name="end_date" :value="old('end_date')" />
    </x-form.field>
</div>
<p class="mt-4 text-body text-muted">Leave End Date empty for an ongoing period. The end date cannot be earlier than the start date.</p>
