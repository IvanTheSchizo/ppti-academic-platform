{{-- Edit Lecturer modal (profile page only). Expects $lecturer. --}}
<x-ui.modal id="edit-lecturer-modal" title="Edit Lecturer" size="lg">
    <form method="POST" action="{{ route('lecturers.update', $lecturer->id) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="grid gap-4 sm:grid-cols-2">
            <x-form.field name="lecturer_code" label="Lecturer Code">
                <x-form.text-input name="lecturer_code" :value="old('lecturer_code', $lecturer->lecturer_code)" required maxlength="20" autocomplete="off" />
            </x-form.field>
            <x-form.field name="name" label="Full Name">
                <x-form.text-input name="name" :value="old('name', $lecturer->name)" required maxlength="255" autocomplete="name" />
            </x-form.field>
            <x-form.field name="latest_education" label="Latest Education">
                <x-form.select name="latest_education">
                    <option value="">Select education</option>
                    @foreach (['Bachelor', 'Master', 'Doctoral', 'Other'] as $education)
                        <option value="{{ $education }}" @selected(old('latest_education', $lecturer->latest_education) === $education)>{{ $education }}</option>
                    @endforeach
                </x-form.select>
            </x-form.field>
            <x-form.field name="jja" label="Academic Functional Rank">
                <x-form.text-input name="jja" :value="old('jja', $lecturer->jja)" maxlength="100" placeholder="e.g. Lektor" />
            </x-form.field>
            <x-form.field name="status" label="Status">
                <x-form.select name="status" required>
                    @foreach (['Active', 'Inactive'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $lecturer->status) === $status)>{{ $status }}</option>
                    @endforeach
                </x-form.select>
            </x-form.field>
            <x-form.field name="gender" label="Gender">
                <x-form.select name="gender">
                    <option value="">Select Gender</option>
                    @foreach (['Male', 'Female'] as $gender)
                        <option value="{{ $gender }}" @selected(old('gender', $lecturer->gender) === $gender)>{{ $gender }}</option>
                    @endforeach
                </x-form.select>
            </x-form.field>
            <x-form.field name="email_binus_edu" label="Email 1">
                <x-form.text-input name="email_binus_edu" type="email" :value="old('email_binus_edu', $lecturer->email_binus_edu)" maxlength="255" autocomplete="email" />
            </x-form.field>
            <x-form.field name="email_binus_ac_id" label="Email 2">
                <x-form.text-input name="email_binus_ac_id" type="email" :value="old('email_binus_ac_id', $lecturer->email_binus_ac_id)" maxlength="255" />
            </x-form.field>
            <x-form.field name="phone_number" label="Phone Number">
                <x-form.text-input name="phone_number" :value="old('phone_number', $lecturer->phone_number)" maxlength="30" autocomplete="tel" />
            </x-form.field>
        </div>

        <div class="flex justify-end gap-3 border-t border-border pt-4">
            <x-ui.button type="button" variant="secondary" data-modal-close>Cancel</x-ui.button>
            <x-ui.button type="submit">Save Changes</x-ui.button>
        </div>
    </form>
</x-ui.modal>
