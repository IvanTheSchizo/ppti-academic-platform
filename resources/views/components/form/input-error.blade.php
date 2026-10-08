@props(['messages' => []])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'space-y-1 text-sm text-danger']) }}>
        @foreach ((array) $messages as $message)
            <li class="flex items-center gap-1.5">
                <x-ui.icon name="warning" class="size-4" />
                {{ $message }}
            </li>
        @endforeach
    </ul>
@endif
