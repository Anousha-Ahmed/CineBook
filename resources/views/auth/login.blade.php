<x-guest-layout :title="__('Sign in — CineBook')">
    <div class="mb-8">
        <p class="eyebrow">{{ __('Welcome back') }}</p>
        <h1 class="mt-3 font-display text-2xl font-semibold tracking-tight text-white">{{ __('Sign in to CineBook') }}</h1>
        <p class="mt-2 text-sm text-slate-400">{{ __('Manage your bookings and pick up where you left off.') }}</p>
    </div>

    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <input type="hidden" name="redirect" value="{{ old('redirect', request('redirect')) }}">

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1.5 block w-full" :value="old('email')" required autofocus autocomplete="username" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-medium text-brand-400 transition hover:text-brand-300" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>
            <x-text-input id="password" name="password" type="password" class="mt-1.5 block w-full" required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label for="remember_me" class="flex cursor-pointer items-center gap-2.5">
            <input id="remember_me" name="remember" type="checkbox" class="h-4 w-4 rounded border-white/20 bg-night-800 text-brand-500 focus:ring-brand-500/40">
            <span class="text-sm text-slate-400">{{ __('Keep me signed in') }}</span>
        </label>

        <x-primary-button class="w-full justify-center">
            {{ __('Sign in') }}
        </x-primary-button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500">
        {{ __('New to CineBook?') }}
        <a href="{{ route('register') }}" class="font-medium text-brand-400 transition hover:text-brand-300">{{ __('Create an account') }}</a>
    </p>
</x-guest-layout>
