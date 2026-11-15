<x-guest-layout :title="__('Reset password — CineBook')">
    <div class="mb-6">
        <p class="eyebrow">{{ __('Account recovery') }}</p>
        <h1 class="mt-3 font-display text-2xl font-semibold tracking-tight text-white">{{ __('Forgot your password?') }}</h1>
        <p class="mt-2 text-sm text-slate-400">
            {{ __('Enter the email you signed up with and we will send a link to choose a new password.') }}
        </p>
    </div>

    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1.5 block w-full" :value="old('email')" required autofocus placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center">
            {{ __('Email reset link') }}
        </x-primary-button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500">
        <a href="{{ route('login') }}" class="font-medium text-brand-400 transition hover:text-brand-300">{{ __('Back to sign in') }}</a>
    </p>
</x-guest-layout>
