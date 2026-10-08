@props(['label'])

<div>
    <dt class="text-body text-muted">{{ $label }}</dt>
    <dd class="text-body text-text">{{ $slot }}</dd>
</div>
