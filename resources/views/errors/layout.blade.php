@extends('layouts.app')

{{-- Shared error page: child views set the icon, code, title and message. --}}
@section('content')
    <div class="flex min-h-[calc(100vh-var(--spacing-navbar)-4rem)] items-center justify-center">
        <div class="flex min-h-100 w-full max-w-md flex-col items-center justify-center rounded-ui bg-surface px-card py-12 text-center shadow-md">
            <span class="grid size-16 place-items-center rounded-full bg-page text-navy">
                <x-ui.icon :name="trim($__env->yieldContent('icon'))" class="size-8" />
            </span>

            <p class="mt-3 text-6xl font-normal text-navy">@yield('code')</p>
            <h1 class="mt-3 text-title font-semibold text-text">@yield('title')</h1>
            <p class="mt-3 max-w-sm text-base text-muted">@yield('message')</p>
        </div>
    </div>
@endsection
