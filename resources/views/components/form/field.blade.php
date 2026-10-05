@props(['name', 'label', 'id' => null, 'caps' => false])

@php
    $id = $id ?? $name;
@endphp

<div {{ $attributes }}>
    <x-form.label :for="$id" :caps="$caps">{{ $label }}</x-form.label>

    {{ $slot }}

    <x-form.input-error :id="$id . '-error'" :messages="isset($errors) ? $errors->get($name) : []" class="mt-1" />
</div>
