<x-guest-layout :title="__('Create account — CineBook')">
    <div class="mb-8">
        <p class="eyebrow">{{ __('Get started') }}</p>
        <h1 class="mt-3 font-display text-2xl font-semibold tracking-tight text-white">{{ __('Create your CineBook account') }}</h1>
        <p class="mt-2 text-sm text-slate-400">{{ __('Book seats, keep your tickets in one place, and skip the queue.') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Full name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1.5 block w-full" :value="old('name')" required autofocus autocomplete="name" placeholder="Ayesha Khan" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1.5 block w-full" :value="old('email')" required autocomplete="username" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" name="password" type="password" class="mt-1.5 block w-full" required autocomplete="new-password" placeholder="At least 8 characters" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm password')" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1.5 block w-full" required autocomplete="new-password" placeholder="Repeat your password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center">
            {{ __('Create account') }}
        </x-primary-button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500">
        {{ __('Already registered?') }}
        <a href="{{ route('login') }}" class="font-medium text-brand-400 transition hover:text-brand-300">{{ __('Sign in') }}</a>
    </p>
</x-guest-layout>
