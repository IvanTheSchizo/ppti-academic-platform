@extends('layouts.app')

@section('title', 'Change Password')
@section('sidebar', 'none')

@section('content')
    <div class="mx-auto w-full max-w-xl">
        <x-ui.card>
            <h1 class="mb-6 flex items-center justify-center gap-3 text-title text-text">
                <x-ui.icon name="lock" class="size-6 text-navy" />
                Change your Password
            </h1>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-6" novalidate>
                @csrf
                @method('PUT')

                <div>
                    <x-form.label for="current_password" :caps="false">Current Password <span class="text-danger">*</span></x-form.label>
                    <x-form.password-input :icon="false" name="current_password" required autofocus autocomplete="current-password" />
                    <x-form.input-error id="current_password-error" :messages="$errors->get('current_password')" class="mt-1" />
                </div>

                <div>
                    <x-form.label for="password" :caps="false">New Password <span class="text-danger">*</span></x-form.label>
                    <x-form.password-input :icon="false" name="password" required autocomplete="new-password" />
                    <x-form.input-error id="password-error" :messages="$errors->get('password')" class="mt-1" />

                    <ul data-password-rules="#password" class="mt-3 space-y-1 text-body">
                        @foreach (['length' => 'At least 8 characters', 'upper' => 'One uppercase letter', 'lower' => 'One lowercase letter', 'symbol' => 'One number or special character'] as $rule => $text)
                            <li data-rule="{{ $rule }}" class="flex items-center gap-2 text-muted">
                                <span class="grid size-5 shrink-0 place-items-center">
                                    <x-ui.icon name="check_small" class="size-5" data-rule-met hidden />
                                    <span class="size-3.5 rounded-full border border-current" data-rule-unmet></span>
                                </span>
                                {{ $text }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <x-form.label for="password_confirmation" :caps="false">Confirm New Password <span class="text-danger">*</span></x-form.label>
                    <x-form.password-input :icon="false" name="password_confirmation" required autocomplete="new-password" />
                    <x-form.input-error id="password_confirmation-error" :messages="$errors->get('password_confirmation')" class="mt-1" />
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <x-ui.button variant="secondary" :href="route('students.index')">Cancel</x-ui.button>
                    <x-ui.button>Update Password</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
