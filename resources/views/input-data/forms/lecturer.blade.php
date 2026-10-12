<div class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
    <x-form.field name="lecturer_code" label="Lecturer Code" :required="true">
        <x-form.text-input name="lecturer_code" :value="old('lecturer_code')" placeholder="e.g. D6767" maxlength="20" autocomplete="off" />
    </x-form.field>

    <x-form.field name="name" label="Full Name" :required="true">
        <x-form.text-input name="name" :value="old('name')" placeholder="e.g. Dr. Hendra Wijaya, S.Kom., M.T." maxlength="255" autocomplete="off" />
    </x-form.field>

    <x-form.field name="jja" label="Academic Functional Rank">
        <x-form.text-input name="jja" :value="old('jja')" placeholder="e.g. Lecturer Specialist – Professor" maxlength="100" autocomplete="off" />
    </x-form.field>

    <x-form.field name="latest_education" label="Latest Education">
        <x-form.select name="latest_education">
            <option value="">Select Latest Education</option>
            @foreach (['Bachelor', 'Master', 'Doctoral', 'Other'] as $education)
                <option value="{{ $education }}" @selected(old('latest_education') === $education)>{{ $education }}</option>
            @endforeach
        </x-form.select>
    </x-form.field>

    <x-form.field name="status" label="Status">
        <x-form.select name="status">
            @foreach (['Active', 'Inactive'] as $status)
                <option value="{{ $status }}" @selected(old('status', 'Active') === $status)>{{ $status }}</option>
            @endforeach
        </x-form.select>
    </x-form.field>

    <x-form.field name="gender" label="Gender">
        <x-form.select name="gender">
            <option value="">Select Gender</option>
            @foreach (['Male', 'Female'] as $gender)
                <option value="{{ $gender }}" @selected(old('gender') === $gender)>{{ $gender }}</option>
            @endforeach
        </x-form.select>
    </x-form.field>

    <x-form.field name="email_binus_edu" label="Email 1">
        <x-form.text-input type="email" name="email_binus_edu" :value="old('email_binus_edu')" placeholder="name@example.com" maxlength="255" autocomplete="off" />
    </x-form.field>

    <x-form.field name="email_binus_ac_id" label="Email 2">
        <x-form.text-input type="email" name="email_binus_ac_id" :value="old('email_binus_ac_id')" placeholder="name@example.com" maxlength="255" autocomplete="off" />
    </x-form.field>

    <x-form.field name="phone_number" label="Phone Number">
        <x-form.text-input name="phone_number" :value="old('phone_number')" placeholder="+62" maxlength="30" autocomplete="off" />
    </x-form.field>
</div>
