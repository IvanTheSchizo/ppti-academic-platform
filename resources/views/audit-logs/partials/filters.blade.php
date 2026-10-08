@php
    $filtersOpen = collect(['action_type', 'target_entity'])->contains(fn ($key) => filled(request()->query($key)));
@endphp

<form method="GET" action="{{ route('audit-logs.index') }}" data-autosubmit class="space-y-4">
    {{-- Preserve active sort query parameters across filter updates --}}
    @if(request()->filled('sort'))
        <input type="hidden" name="sort" value="{{ request('sort') }}">
    @endif
    @if(request()->filled('direction'))
        <input type="hidden" name="direction" value="{{ request('direction') }}">
    @endif

    <div class="flex items-center gap-3">
        <div class="flex-1">
            <x-form.search-input name="q" :value="request()->query('q')" placeholder="Search logs..." aria-label="Search audit logs" />
        </div>

        <x-ui.button type="button" variant="secondary" data-toggle="#audit-log-filters" aria-controls="audit-log-filters" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}">
            <x-ui.icon name="filter_alt" /> Filter
        </x-ui.button>
    </div>

    <div id="audit-log-filters" @unless ($filtersOpen) hidden @endunless class="grid grid-cols-1 gap-4 sm:grid-cols-2 rounded-ui border border-border bg-surface p-card shadow-sm">
        <div>
            <x-form.label for="action_type">Action</x-form.label>
            <x-form.select name="action_type">
                <option value="">All</option>
                @foreach ($actionOptions ?? [] as $option)
                    <option value="{{ $option }}" @selected(request()->query('action_type') === $option)>{{ $option }}</option>
                @endforeach
            </x-form.select>
        </div>

        <div>
            <x-form.label for="target_entity">Target Entity</x-form.label>
            <x-form.select name="target_entity">
                <option value="">All</option>
                @foreach ($entityOptions ?? [] as $option)
                    <option value="{{ $option }}" @selected(request()->query('target_entity') === $option)>{{ $option }}</option>
                @endforeach
            </x-form.select>
        </div>
    </div>
</form>