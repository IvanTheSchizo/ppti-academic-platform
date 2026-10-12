@props(['name', 'label', 'id' => null, 'caps' => false, 'required' => false])

@php
    $id = $id ?? $name;
@endphp

<div {{ $attributes }}>
    <x-form.label :for="$id" :caps="$caps">{{ $label }}@if ($required) <span class="text-danger" aria-hidden="true">*</span>@endif</x-form.label>

    {{ $slot }}

    <x-form.input-error :id="$id . '-error'" :messages="isset($errors) ? $errors->get($name) : []" class="mt-1" />
</div>
