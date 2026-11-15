<x-guest-layout :title="__('Verify email — CineBook')">
    <div class="mb-6">
        <p class="eyebrow">{{ __('One more step') }}</p>
        <h1 class="mt-3 font-display text-2xl font-semibold tracking-tight text-white">{{ __('Verify your email') }}</h1>
        <p class="mt-2 text-sm leading-relaxed text-slate-400">
            {{ __("Thanks for signing up. Click the link we emailed you to verify your address. If it didn't arrive, we can send another.") }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-5 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
            {{ __('A new verification link has been sent to the email address you provided.') }}
        </div>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>{{ __('Resend email') }}</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm font-medium text-slate-500 transition hover:text-slate-300">
                {{ __('Log out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
