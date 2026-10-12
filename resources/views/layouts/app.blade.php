<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col font-sans antialiased">
    @php($withSidebar = trim($__env->yieldContent('sidebar')) !== 'none')

    <x-layout.navbar :sidebar="$withSidebar" />
    <x-ui.flash />

    <div class="flex flex-1">
        @if ($withSidebar)
            <x-layout.sidebar />
        @endif

        <main class="min-w-0 flex-1 p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>
    </div>
</body>
</html>
