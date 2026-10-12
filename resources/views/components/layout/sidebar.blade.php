@php
    $links = [
        ['Student', 'students.index', 'person'],
        ['Lecturer', 'lecturers.index', 'badge'],
        ['Course Records', 'course-records.index', 'school'],
        ['Audit Log', 'audit-logs.index', 'history'],
    ];
@endphp

<div data-sidebar-backdrop hidden class="fixed inset-x-0 top-navbar bottom-0 z-20 bg-black/50 lg:hidden"></div>

<aside id="sidebar" data-sidebar
       class="w-sidebar overflow-y-auto bg-primary pt-6 text-on-primary
              max-lg:invisible max-lg:fixed max-lg:top-navbar max-lg:bottom-0 max-lg:left-0 max-lg:z-30 max-lg:-translate-x-full max-lg:transition-transform max-lg:data-open:visible max-lg:data-open:translate-x-0
              lg:sticky lg:top-navbar lg:h-[calc(100vh-var(--spacing-navbar))] lg:shrink-0 lg:self-start">
    <nav aria-label="Main" class="flex flex-col gap-2 pl-2">
        @foreach ($links as [$label, $route, $icon])
            @if (Route::has($route))
                @php $active = request()->routeIs($route, Str::before($route, '.') . '.*'); @endphp
                <a href="{{ route($route) }}"
                   @if ($active) aria-current="page" @endif
                   class="flex h-control items-center gap-3 rounded-l-xl px-4 text-sidebar font-normal transition-colors focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-on-primary
                          {{ $active ? 'bg-navy' : 'hover:bg-primary-hover active:bg-primary-active' }}">
                    <x-ui.icon :name="$icon" class="size-6" />
                    {{ $label }}
                </a>
            @endif
        @endforeach
    </nav>
</aside>
