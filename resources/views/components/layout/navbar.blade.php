@php
    $home = Route::has('students.index') ? route('students.index') : url('/');
@endphp

<header class="sticky top-0 z-30 flex h-navbar shrink-0 items-center justify-between gap-3 border-b border-border bg-surface px-4 sm:px-6">
    <div class="flex min-w-0 items-center gap-2 sm:gap-4">
        <button type="button" data-sidebar-toggle aria-label="Toggle menu" aria-controls="sidebar" aria-expanded="false"
                class="grid size-10 shrink-0 place-items-center rounded-ui text-text transition-colors hover:bg-page active:bg-border/50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary lg:hidden">
            <x-ui.icon name="menu" class="size-6" />
        </button>

        <x-layout.brand :href="$home" compact />
    </div>

    <details data-dropdown class="relative shrink-0">
        <summary aria-label="Account menu" class="flex size-10 cursor-pointer list-none items-center justify-center rounded-full text-text transition-colors hover:text-navy active:text-navy-active focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary [&::-webkit-details-marker]:hidden">
            <x-ui.icon name="account_circle" class="size-8" />
        </summary>

        <div class="absolute right-0 z-10 mt-2 w-56 rounded-ui border border-border bg-surface p-3">
            @auth
                <p class="mb-3 truncate text-body text-muted">{{ auth()->user()->username ?? auth()->user()->name }}</p>
            @endauth

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-ui.button variant="secondary" class="w-full">Logout</x-ui.button>
            </form>
        </div>
    </details>
</header>
