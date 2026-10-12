<div class="grid gap-4 sm:grid-cols-2">
    <x-form.field name="nip" label="NIP">
        <x-form.text-input name="nip" required maxlength="50" autocomplete="off" data-lecturer-field="nip" />
    </x-form.field>
    <x-form.field name="lecturer_code" label="Lecturer Code">
        <x-form.text-input name="lecturer_code" required maxlength="20" autocomplete="off" data-lecturer-field="lecturer_code" />
    </x-form.field>
    <div class="sm:col-span-2">
        <x-form.field name="name" label="Full Name">
            <x-form.text-input name="name" required maxlength="255" autocomplete="name" data-lecturer-field="name" />
        </x-form.field>
    </div>
    <x-form.field name="email_binus_edu" label="BINUS EDU Email">
        <x-form.text-input name="email_binus_edu" type="email" required maxlength="255" autocomplete="email" data-lecturer-field="email_binus_edu" />
    </x-form.field>
    <x-form.field name="email_binus_ac_id" label="BINUS AC.ID Email">
        <x-form.text-input name="email_binus_ac_id" type="email" required maxlength="255" data-lecturer-field="email_binus_ac_id" />
    </x-form.field>
    <x-form.field name="phone_number" label="Phone Number">
        <x-form.text-input name="phone_number" required maxlength="30" autocomplete="tel" data-lecturer-field="phone_number" />
    </x-form.field>
    <x-form.field name="jja" label="JJA (Academic Rank)">
        <x-form.text-input name="jja" required maxlength="100" placeholder="e.g. Lektor" data-lecturer-field="jja" />
    </x-form.field>
    <x-form.field name="latest_education" label="Latest Education">
        <x-form.select name="latest_education" required data-lecturer-field="latest_education">
            <option value="">Select education</option>
            @foreach (['Bachelor', 'Master', 'Doctoral', 'Other'] as $education)
                <option value="{{ $education }}">{{ $education }}</option>
            @endforeach
        </x-form.select>
    </x-form.field>
    <x-form.field name="status" label="Status">
        <x-form.select name="status" required data-lecturer-field="status">
            @foreach (['Active', 'Inactive', 'On Leave'] as $status)
                <option value="{{ $status }}">{{ $status }}</option>
            @endforeach
        </x-form.select>
    </x-form.field>
</div>
