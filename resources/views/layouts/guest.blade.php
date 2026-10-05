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
<body class="bg-primary font-sans antialiased">
    <x-ui.flash />
    <div class="flex min-h-screen items-center justify-center px-4">
        <div class="w-full max-w-sm">
            @yield('content')
        </div>
    </div>
</body>
</html>
