@props(['sidebar' => true])

@php
    $home = Route::has('students.index') ? route('students.index') : url('/');
    $now = now();
    $hour = $now->hour;
    $greeting = match (true) {
        $hour >= 5 && $hour < 12 => 'Good morning',
        $hour >= 12 && $hour < 18 => 'Good afternoon',
        default => 'Good evening',
    };
    $admin = auth()->user();
    $adminName = $admin?->displayName() ?? 'Admin';
    $adminPhotoUrl = $admin?->photoUrl();
@endphp

<header class="sticky top-0 z-30 flex h-navbar shrink-0 items-center justify-between gap-3 border-b border-border bg-surface px-4 sm:px-6">
    <div class="flex min-w-0 items-center gap-2 sm:gap-4">
        @if ($sidebar)
            <button type="button" data-sidebar-toggle aria-label="Toggle menu" aria-controls="sidebar" aria-expanded="false"
                    class="grid size-10 shrink-0 place-items-center rounded-ui text-text transition-colors hover:bg-page active:bg-border/50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary lg:hidden">
                <x-ui.icon name="menu" class="size-6" />
            </button>
        @endif

        <x-layout.brand :href="$home" compact />
    </div>

    <div class="flex items-center gap-3 sm:gap-4">
        @auth
            <div class="hidden text-right sm:block">
                <p class="text-body font-medium text-text" data-server-greeting data-admin-name="{{ $adminName }}">
                    {{ $greeting }}, {{ $adminName }}
                </p>
                <p class="text-caption tabular-nums text-muted"
                   data-server-clock
                   data-server-time="{{ $now->getTimestampMs() }}"
                   data-server-tz="{{ config('app.timezone') }}">
                    {{ $now->format('D, d M Y · H:i:s') }}
                </p>
            </div>
        @endauth

        <details data-dropdown class="relative shrink-0">
            <summary aria-label="Account menu" class="flex size-10 cursor-pointer list-none items-center justify-center rounded-full text-text transition-colors hover:text-navy active:text-navy-active focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary [&::-webkit-details-marker]:hidden">
                @if ($adminPhotoUrl)
                    <x-ui.avatar :src="$adminPhotoUrl" :alt="$adminName" size="md" />
                @else
                    <x-ui.icon name="account_circle" class="size-8" />
                @endif
            </summary>

            <div class="absolute right-0 z-10 mt-2 w-60 rounded-ui border border-border bg-surface p-3 shadow-sm">
                @auth
                    <div class="mb-3 border-b border-border pb-2">
                        <p class="truncate text-body font-medium text-text">{{ $adminName }}</p>
                        @if ($admin->display_name)
                            <p class="truncate text-caption text-muted">{{ $admin->username }}</p>
                        @endif
                    </div>
                @endauth

                <div class="flex flex-col gap-2">
                    <x-ui.button type="button" variant="secondary" data-modal-open="#edit-display-name-modal" class="w-full justify-start! gap-3">
                        <x-ui.icon name="pencil" class="size-5" />
                        Change Display Name
                    </x-ui.button>

                    <x-ui.button type="button" variant="secondary" data-modal-open="#edit-admin-photo-modal" class="w-full justify-start! gap-3">
                        <x-ui.icon name="account_circle" class="size-5" />
                        Change Photo
                    </x-ui.button>

                    <x-ui.button variant="secondary" :href="route('password.edit')" class="w-full justify-start! gap-3">
                        <x-ui.icon name="lock" class="size-5" />
                        Change Password
                    </x-ui.button>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-ui.button variant="secondary" class="w-full justify-start! gap-3">
                            <x-ui.icon name="logout" class="size-5" />
                            Logout
                        </x-ui.button>
                    </form>
                </div>
            </div>
        </details>
    </div>
</header>

@auth
    <x-ui.modal id="edit-display-name-modal" title="Change Display Name" size="sm">
        <form method="POST" action="{{ route('account.display-name.update') }}" id="edit-display-name-form" class="space-y-4">
            @csrf
            @method('PUT')

            <x-form.field name="display_name" label="Display Name">
                <x-form.text-input name="display_name" :value="$admin->display_name" placeholder="Leave blank to use {{ $admin->username }}" maxlength="100" />
            </x-form.field>
        </form>

        <x-slot:footer>
            <x-ui.button type="button" variant="secondary" data-modal-close>Cancel</x-ui.button>
            <x-ui.button type="submit" form="edit-display-name-form">Save</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <x-ui.photo-modal
        id="edit-admin-photo-modal"
        title="Account Profile Photo"
        :src="$adminPhotoUrl"
        :alt="$adminName"
        :upload-action="route('account.photo.update')"
        :delete-action="route('account.photo.destroy')"
    />
@endauth
