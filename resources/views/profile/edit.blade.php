<x-app-layout>
    <x-slot name="header">
        <p class="eyebrow">{{ __('Account') }}</p>
        <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-white">{{ __('Profile settings') }}</h1>
        <p class="mt-2 text-sm text-slate-400">{{ __('Update your details, change your password, or close your account.') }}</p>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-6">
        <div class="card p-6 sm:p-8">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="card p-6 sm:p-8">
            @include('profile.partials.update-password-form')
        </div>

        <div class="card border-red-500/15 p-6 sm:p-8">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
