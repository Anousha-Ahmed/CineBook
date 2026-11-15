<x-guest-layout :title="__('Choose a new password — CineBook')">
    <div class="mb-6">
        <p class="eyebrow">{{ __('Account recovery') }}</p>
        <h1 class="mt-3 font-display text-2xl font-semibold tracking-tight text-white">{{ __('Choose a new password') }}</h1>
        <p class="mt-2 text-sm text-slate-400">{{ __('Use a password you have not used on CineBook before.') }}</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1.5 block w-full" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('New password')" />
            <x-text-input id="password" name="password" type="password" class="mt-1.5 block w-full" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm new password')" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1.5 block w-full" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center">
            {{ __('Reset password') }}
        </x-primary-button>
    </form>
</x-guest-layout>
