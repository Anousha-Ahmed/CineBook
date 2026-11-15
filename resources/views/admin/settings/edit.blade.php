<x-layouts.admin heading="Settings" subheading="Business rules used across booking, payment, and scanning">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-3xl space-y-6">
        @csrf
        @method('put')

        <div class="card p-6">
            <h2 class="font-display text-base font-semibold text-white">Booking rules</h2>
            <p class="mt-1 text-xs text-slate-500">These values drive the seat map, checkout timer, and pricing.</p>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="hold_minutes" class="label">Seat hold (minutes)</label>
                    <input id="hold_minutes" name="hold_minutes" type="number" min="1" max="60" value="{{ old('hold_minutes', $settings['hold_minutes']) }}" class="input mt-1.5 w-full" required>
                    <p class="mt-1.5 text-xs text-slate-500">How long a customer has to finish checkout.</p>
                    @error('hold_minutes') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="max_seats" class="label">Max seats per booking</label>
                    <input id="max_seats" name="max_seats" type="number" min="1" max="12" value="{{ old('max_seats', $settings['max_seats']) }}" class="input mt-1.5 w-full" required>
                    <p class="mt-1.5 text-xs text-slate-500">Applies per booking, per customer.</p>
                    @error('max_seats') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="cancel_cutoff_hours" class="label">Cancellation cutoff (hours)</label>
                    <input id="cancel_cutoff_hours" name="cancel_cutoff_hours" type="number" min="0" max="48" value="{{ old('cancel_cutoff_hours', $settings['cancel_cutoff_hours']) }}" class="input mt-1.5 w-full" required>
                    <p class="mt-1.5 text-xs text-slate-500">Customers cannot cancel within this window.</p>
                    @error('cancel_cutoff_hours') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="premium_surcharge_cents" class="label">Premium surcharge (cents)</label>
                    <input id="premium_surcharge_cents" name="premium_surcharge_cents" type="number" min="0" max="10000" step="50" value="{{ old('premium_surcharge_cents', $settings['premium_surcharge_cents']) }}" class="input mt-1.5 w-full" required>
                    <p class="mt-1.5 text-xs text-slate-500">Added to the base price for premium rows.</p>
                    @error('premium_surcharge_cents') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="card p-6">
            <h2 class="font-display text-base font-semibold text-white">Entry scanning</h2>
            <p class="mt-1 text-xs text-slate-500">The scanning window is fixed by the cinema policy.</p>

            <ul class="mt-4 space-y-2 text-sm text-slate-400">
                <li class="flex justify-between border-b border-white/[.05] pb-2">
                    <span>Opens before showtime</span>
                    <span class="font-medium text-white">{{ \App\Support\BookingRules::SCAN_OPEN_MINUTES }} minutes</span>
                </li>
                <li class="flex justify-between border-b border-white/[.05] pb-2">
                    <span>Closes after start time</span>
                    <span class="font-medium text-white">{{ \App\Support\BookingRules::SCAN_CLOSE_MINUTES }} minutes</span>
                </li>
                <li class="flex justify-between">
                    <span>Sales close before showtime</span>
                    <span class="font-medium text-white">{{ \App\Support\BookingRules::SALES_CUTOFF_MINUTES }} minutes</span>
                </li>
            </ul>
        </div>

        <div class="card p-6">
            <h2 class="font-display text-base font-semibold text-white">Payments</h2>
            <div class="mt-5 max-w-xs">
                <label for="currency" class="label">Currency</label>
                <select id="currency" name="currency" class="input mt-1.5 w-full" required>
                    @foreach (['usd' => 'US Dollar (USD)', 'eur' => 'Euro (EUR)', 'gbp' => 'British Pound (GBP)', 'pkr' => 'Pakistani Rupee (PKR)'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('currency', $settings['currency']) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('currency') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn btn-primary">Save settings</button>
        </div>
    </form>
</x-layouts.admin>
