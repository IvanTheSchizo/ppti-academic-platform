<div class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
    <x-form.field name="course_code" label="Course Code" :required="true">
        <x-form.text-input name="course_code" :value="old('course_code')" placeholder="e.g. COMP6047" maxlength="20" autocomplete="off" />
    </x-form.field>

    <x-form.field name="course_name" label="Course Name" :required="true">
        <x-form.text-input name="course_name" :value="old('course_name')" placeholder="e.g. Algorithm and Programming" maxlength="255" autocomplete="off" />
    </x-form.field>

    <x-form.field name="sks" label="SCU" :required="true">
        <x-form.text-input name="sks" :value="old('sks')" placeholder="e.g. 2/4" maxlength="10" autocomplete="off" />
    </x-form.field>
</div>
