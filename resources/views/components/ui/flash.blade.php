@php
    $messages = array_filter([
        'success' => session('success'),
        'danger' => session('error'),
        'warning' => session('warning'),
        'info' => session('status'),
    ]);
@endphp

@if ($messages)
    <div data-flash aria-live="polite" class="fixed top-[calc(var(--spacing-navbar)+1rem)] right-4 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3">
        @foreach ($messages as $variant => $message)
            <x-ui.toast :variant="$variant">{{ $message }}</x-ui.toast>
        @endforeach
    </div>
@endif
