<form method="GET" action="{{ route('audit-logs.index') }}" data-autosubmit class="flex flex-wrap items-center gap-3">
    {{-- Preserve active sort across filter updates --}}
    @if (request()->filled('sort'))
        <input type="hidden" name="sort" value="{{ request('sort') }}">
    @endif
    @if (request()->filled('direction'))
        <input type="hidden" name="direction" value="{{ request('direction') }}">
    @endif

    <x-form.date-range name="date" :from="request()->query('date_from')" :to="request()->query('date_to')" class="w-full sm:w-80" />

    <x-form.select name="admin_id" aria-label="Filter by admin" class="sm:w-56">
        <option value="">All Admins</option>
        @foreach ($adminOptions ?? [] as $admin)
            <option value="{{ $admin->id }}" @selected((string) request()->query('admin_id') === (string) $admin->id)>{{ $admin->username }}</option>
        @endforeach
    </x-form.select>

    <x-form.select name="action_type" aria-label="Filter by action" class="sm:w-56">
        <option value="">All Actions</option>
        @foreach ($actionOptions ?? [] as $option)
            <option value="{{ $option }}" @selected(request()->query('action_type') === $option)>{{ ucfirst(strtolower($option)) }}</option>
        @endforeach
    </x-form.select>

    <x-ui.button variant="secondary" type="button" :href="route('audit-logs.index')">
        <x-ui.icon name="filter_alt_off" /> Reset
    </x-ui.button>
</form>
