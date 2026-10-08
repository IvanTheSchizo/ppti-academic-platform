@props(['paginator', 'perPageOptions' => [10, 25, 50, 100]])

@php
    $page = $paginator->currentPage();
    $last = max($paginator->lastPage(), 1);

    $controls = [
        ['first_page', 'First page', 1, $page > 1],
        ['chevron_left', 'Previous page', $page - 1, $page > 1],
        ['chevron_right', 'Next page', $page + 1, $page < $last],
        ['last_page', 'Last page', $last, $page < $last],
    ];
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center justify-end gap-x-6 gap-y-2 text-body']) }}>
    <span>{{ $paginator->count() }} Result(s)</span>

    <label class="flex items-center gap-2 text-muted">
        Show:
        <x-form.select size="sm" data-navigate>
            @foreach ($perPageOptions as $option)
                <option value="{{ request()->fullUrlWithQuery(['per_page' => $option, 'page' => 1]) }}" @selected($option == $paginator->perPage())>{{ $option }}</option>
            @endforeach
        </x-form.select>
    </label>

    <nav aria-label="Pagination" class="flex items-center gap-1">
        @foreach ($controls as [$icon, $label, $target, $enabled])
            @if ($enabled)
                <a href="{{ $paginator->url($target) }}" aria-label="{{ $label }}"
                   class="grid size-8 place-items-center rounded-ui text-text transition-colors hover:bg-page active:bg-border/50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                    <x-ui.icon :name="$icon" />
                </a>
            @else
                <span aria-label="{{ $label }}" aria-disabled="true" class="grid size-8 place-items-center text-muted opacity-40">
                    <x-ui.icon :name="$icon" />
                </span>
            @endif
        @endforeach
    </nav>

    <label class="flex items-center gap-2 text-muted">
        Page:
        <x-form.select size="sm" data-navigate>
            @for ($i = 1; $i <= $last; $i++)
                <option value="{{ $paginator->url($i) }}" @selected($i === $page)>{{ $i }}</option>
            @endfor
        </x-form.select>
    </label>
</div>
