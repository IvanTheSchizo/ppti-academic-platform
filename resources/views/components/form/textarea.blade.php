@props(['disabled' => false])

@php
    $name = $attributes->get('name');
    $id = $attributes->get('id', $name);
    $invalid = isset($errors) && $name && $errors->has($name);

    $state = $invalid
        ? 'border-danger focus:outline-danger/30'
        : 'border-border enabled:hover:border-muted focus:border-primary focus:outline-primary/30';
@endphp

<textarea @disabled($disabled)
          @if ($invalid) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
          {{ $attributes->merge(['id' => $id, 'rows' => 4, 'class' => "min-h-24 w-full resize-y rounded-ui border bg-surface px-3 py-2 text-body text-text placeholder:text-muted focus:outline-2 disabled:cursor-not-allowed disabled:bg-page disabled:text-muted {$state}"]) }}>{{ $slot }}</textarea>
