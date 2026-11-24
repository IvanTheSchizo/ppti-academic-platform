@php
    $normalizeFilter = fn (string $key) => array_values(array_map(
        'strval',
        array_filter((array) request()->query($key, []), fn ($v) => is_scalar($v) && trim((string) $v) !== '')
    ));

    $selectedAdmins = $normalizeFilter('admin_id');
    $selectedActions = $normalizeFilter('action_type');

    $adminSelectOptions = collect($adminOptions ?? [])->map(fn ($admin) => [
        'value' => (string) $admin->id,
        'label' => $admin->username,
    ])->all();

    $actionSelectOptions = collect($actionOptions ?? [])->map(fn ($option) => [
        'value' => $option,
        'label' => ucfirst(strtolower($option)),
    ])->all();
@endphp

<form method="GET" action="{{ route('audit-logs.index') }}" data-autosubmit class="flex flex-wrap items-center gap-3">
    {{-- Preserve active sort across filter updates --}}
    @if (request()->filled('sort'))
        <input type="hidden" name="sort" value="{{ request('sort') }}">
    @endif
    @if (request()->filled('direction'))
        <input type="hidden" name="direction" value="{{ request('direction') }}">
    @endif

    <x-form.date-range name="date" :from="request()->query('date_from')" :to="request()->query('date_to')" class="w-full sm:w-80" />

    <x-form.multi-select
        name="admin_id[]"
        :options="$adminSelectOptions"
        :selected="$selectedAdmins"
        placeholder="All Admins"
        search-placeholder="Search admins..."
        empty-message="No admins available."
        aria-label="Filter by admin"
        class="w-full sm:w-56"
    />

    <x-form.multi-select
        name="action_type[]"
        :options="$actionSelectOptions"
        :selected="$selectedActions"
        placeholder="All Actions"
        empty-message="No actions available."
        :searchable="false"
        aria-label="Filter by action"
        class="w-full sm:w-56"
    />

    <x-ui.button variant="secondary" type="button" :href="route('audit-logs.index')">
        <x-ui.icon name="filter_alt_off" /> Reset
    </x-ui.button>
</form>
