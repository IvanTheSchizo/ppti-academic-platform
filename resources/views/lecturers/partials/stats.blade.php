<div class="flex flex-wrap items-center gap-x-10 gap-y-2">
    <x-ui.stat label="Total" :value="$stats['total']" />
    <x-ui.stat label="Active" :value="$stats['active']" color="success" />
    <x-ui.stat label="Inactive" :value="$stats['inactive']" color="danger" />
</div>