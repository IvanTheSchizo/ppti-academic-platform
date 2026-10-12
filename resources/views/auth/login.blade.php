@extends('layouts.guest')

@section('title', 'Admin Login')

@section('content')
    <div class="rounded-xl bg-surface p-6 shadow-xl sm:p-10">
        <h1 class="sr-only">Admin Login</h1>

        <x-layout.brand class="mb-6" />

        <form method="POST" action="{{ route('login.submit') }}" class="space-y-3">
            @csrf

            <div>
                <x-form.icon-input icon="person" type="text" name="username" :value="old('username')" placeholder="Username" aria-label="Username" size="lg" :filled="true" required autofocus autocomplete="username" />
                <x-form.input-error id="username-error" :messages="$errors->get('username')" class="mt-1" />
            </div>

            <div>
                <x-form.password-input name="password" placeholder="Password" aria-label="Password" size="lg" :filled="true" required autocomplete="current-password" />
                <x-form.input-error id="password-error" :messages="$errors->get('password')" class="mt-1" />
            </div>

            <div class="pt-2">
                <x-ui.button size="lg" class="w-full uppercase tracking-wide">Sign in</x-ui.button>
            </div>
        </form>
    </div>
@endsection
