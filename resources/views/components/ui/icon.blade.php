@props(['name'])

@php
    $file = preg_match('/^[A-Za-z0-9_-]+$/', $name) ? resource_path("svg/{$name}.svg") : null;
    $svg = $file && is_file($file) ? file_get_contents($file) : null;

    $sizeClass = str_contains($attributes->get('class', ''), 'size-') ? '' : 'size-5';
    $attrs = $attributes->merge(['class' => "shrink-0 {$sizeClass}"])->toHtml();

    if ($svg) {
        $svg = preg_replace_callback('/<svg\b[^>]*>/i', function ($match) use ($attrs) {
            $tag = preg_replace('/\s(?:width|height|class)="[^"]*"/i', '', $match[0]);
            $tag = preg_replace('/\sfill="(?!none")[^"]*"/i', ' fill="currentColor"', $tag);

            return rtrim($tag, '>') . ' aria-hidden="true" ' . $attrs . '>';
        }, $svg, 1);
    }
@endphp

@if ($svg)
    {!! $svg !!}
@elseif (app()->isLocal())
    <!-- icon "{{ $name }}" not found in resources/svg -->
@endif
