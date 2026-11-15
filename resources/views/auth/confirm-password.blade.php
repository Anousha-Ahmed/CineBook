<x-guest-layout :title="__('Confirm password — CineBook')">
    <div class="mb-6">
        <p class="eyebrow">{{ __('Security check') }}</p>
        <h1 class="mt-3 font-display text-2xl font-semibold tracking-tight text-white">{{ __('Confirm your password') }}</h1>
        <p class="mt-2 text-sm text-slate-400">{{ __('This is a protected area. Please re-enter your password to continue.') }}</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" name="password" type="password" class="mt-1.5 block w-full" required autofocus autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center">{{ __('Confirm') }}</x-primary-button>
    </form>
</x-guest-layout>
