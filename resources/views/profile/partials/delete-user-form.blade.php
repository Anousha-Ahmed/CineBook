<section class="space-y-5">
    <header>
        <h2 class="font-display text-lg font-semibold text-white">{{ __('Delete account') }}</h2>
        <p class="mt-1 text-sm text-slate-400">
            {{ __('Deleting your account permanently removes your profile, bookings, and tickets. Download anything you want to keep first.') }}
        </p>
    </header>

    <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
        {{ __('Delete account') }}
    </x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="font-display text-lg font-semibold text-white">{{ __('Delete your account?') }}</h2>
            <p class="mt-2 text-sm text-slate-400">
                {{ __('This cannot be undone. Enter your password to confirm you want to permanently delete your account.') }}
            </p>

            <div class="mt-5">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" placeholder="{{ __('Password') }}" />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                <x-danger-button>{{ __('Delete account') }}</x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
