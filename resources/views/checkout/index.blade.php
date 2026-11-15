@php
    use App\Services\PricingService;
@endphp

<x-layouts.public title="Checkout — CineBook">
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="eyebrow">Step 2 of 3</p>
                <h1 class="mt-2 font-display text-2xl font-semibold tracking-tight text-white sm:text-3xl">Review &amp; pay</h1>
                <p class="mt-2 text-sm text-slate-400">Your seats are reserved. Complete payment before the hold expires.</p>
            </div>

            <div class="flex items-center gap-2">
                <span class="badge badge-slate">1 Seats</span>
                <span class="badge badge-blue">2 Payment</span>
                <span class="badge badge-slate">3 Ticket</span>
            </div>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <div class="space-y-6">
                <div class="card overflow-hidden">
                    <div class="flex items-center gap-4 border-b border-white/[.06] p-5">
                        <div class="h-24 w-16 shrink-0 overflow-hidden rounded-lg bg-night-800">
                            @if ($showtime->movie->poster_url)
                                <img src="{{ $showtime->movie->poster_url }}" alt="{{ $showtime->movie->title }}" class="h-full w-full object-cover">
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] uppercase tracking-widest text-brand-400">CineBook Downtown</p>
                            <h2 class="mt-1 truncate font-display text-lg font-semibold text-white">{{ $showtime->movie->title }}</h2>
                            <p class="mt-1 text-xs text-slate-400">
                                {{ $showtime->start_time->format('D j M Y, g:i A') }} &middot; {{ $showtime->screen->name }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $showtime->movie->duration_minutes }} min &middot; {{ $showtime->movie->rating }}</p>
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-white">Your tickets</h3>
                            <a href="{{ route('booking.seats', $showtime) }}" class="text-xs font-medium text-brand-400 transition hover:text-brand-300">Change seats</a>
                        </div>

                        <ul class="mt-4 divide-y divide-white/[.06]">
                            @foreach ($holds->sortBy(fn ($h) => $h->seat->row.$h->seat->number) as $hold)
                                @php
                                    $price = PricingService::seatPriceCents($showtime, $hold->seat);
                                @endphp
                                <li class="flex items-center justify-between gap-3 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 bg-white/[.03] text-xs font-bold text-white">
                                            {{ $hold->seat->label }}
                                        </span>
                                        <div>
                                            <p class="text-sm text-slate-200">{{ $hold->seat->is_premium ? 'Premium seat' : 'Standard seat' }}</p>
                                            <p class="text-[11px] text-slate-500">Row {{ $hold->seat->row }}, seat {{ $hold->seat->number }}</p>
                                        </div>
                                    </div>
                                    <span class="text-sm font-semibold text-white">{{ PricingService::format($price) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="card p-5">
                    <h3 class="text-sm font-semibold text-white">Before you pay</h3>
                    <ul class="mt-3 space-y-2 text-xs leading-relaxed text-slate-500">
                        <li class="flex gap-2"><span class="text-brand-400">&bull;</span> Tickets are e-delivered instantly. Your QR code is your entry pass.</li>
                        <li class="flex gap-2"><span class="text-brand-400">&bull;</span> Doors open 30 minutes before the show; scanning closes 30 minutes after start.</li>
                        <li class="flex gap-2"><span class="text-brand-400">&bull;</span> Free cancellation up to {{ \App\Services\SettingsService::get('cancel_cutoff_hours', 2) }} hours before showtime.</li>
                        <li class="flex gap-2"><span class="text-brand-400">&bull;</span> This is Stripe test mode &mdash; no real money is charged.</li>
                    </ul>
                </div>
            </div>

            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="card p-5">
                    <div class="flex items-center justify-between">
                        <h2 class="font-display text-base font-semibold text-white">Order summary</h2>
                        <span id="hold-timer" data-seconds="{{ $secondsRemaining }}"
                              class="badge badge-amber">
                            {{ gmdate('i:s', max(0, $secondsRemaining)) }}
                        </span>
                    </div>

                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between text-slate-400">
                            <dt>{{ $holds->count() }} {{ \Illuminate\Support\Str::plural('ticket', $holds->count()) }}</dt>
                            <dd>{{ PricingService::format($totalCents) }}</dd>
                        </div>
                        <div class="flex justify-between text-slate-400">
                            <dt>Booking fee</dt>
                            <dd>{{ PricingService::format(0) }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between border-t border-white/[.07] pt-3">
                            <dt class="font-medium text-white">Total</dt>
                            <dd class="font-display text-2xl font-semibold text-white">{{ PricingService::format($totalCents) }}</dd>
                        </div>
                    </dl>

                    <form action="{{ route('checkout.process') }}" method="POST" class="mt-5">
                        @csrf
                        <button id="pay-btn" type="submit" class="btn btn-primary w-full">
                            Pay {{ PricingService::format($totalCents) }} with Stripe
                        </button>
                    </form>

                    <p class="mt-3 flex items-center justify-center gap-1.5 text-[11px] text-slate-500">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="10" width="16" height="10" rx="2" /><path d="M8 10V7a4 4 0 0 1 8 0v3" /></svg>
                        Secure checkout powered by Stripe
                    </p>

                    <p id="hold-expired" class="mt-3 hidden rounded-lg border border-red-500/25 bg-red-500/10 px-3 py-2 text-[11px] text-red-200">
                        Your hold has expired. Go back and select your seats again.
                    </p>
                </div>
            </aside>
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const el = document.getElementById('hold-timer');
                const payBtn = document.getElementById('pay-btn');
                const expired = document.getElementById('hold-expired');
                let remaining = Number(el.dataset.seconds);

                const paint = () => {
                    if (remaining <= 0) {
                        el.textContent = 'Expired';
                        el.className = 'badge badge-red';
                        payBtn.disabled = true;
                        payBtn.classList.add('opacity-40');
                        expired.classList.remove('hidden');
                        return false;
                    }

                    const m = Math.floor(remaining / 60).toString().padStart(2, '0');
                    const s = (remaining % 60).toString().padStart(2, '0');
                    el.textContent = `${m}:${s}`;
                    el.className = remaining < 120 ? 'badge badge-red' : 'badge badge-amber';

                    return true;
                };

                if (paint()) {
                    const timer = setInterval(() => {
                        remaining -= 1;
                        if (!paint()) clearInterval(timer);
                    }, 1000);
                }
            })();
        </script>
    @endpush
</x-layouts.public>
