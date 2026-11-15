@php
    use App\Services\PricingService;
@endphp

<x-layouts.public :title="'Booking #' . $booking->id . ' — CineBook'">
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <a href="{{ route('bookings.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-400 transition hover:text-white">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m6 6-6-6 6-6" stroke-linecap="round" stroke-linejoin="round" /></svg>
            My bookings
        </a>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="eyebrow">Booking #{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</p>
                <h1 class="mt-2 font-display text-2xl font-semibold tracking-tight text-white sm:text-3xl">{{ $booking->showtime->movie->title }}</h1>
                <p class="mt-1 text-sm text-slate-400">
                    {{ $booking->showtime->start_time->format('l, j M Y, g:i A') }} &middot; {{ $booking->showtime->screen->name }}
                </p>
            </div>
            <span class="{{ $booking->statusClasses() }}">{{ $booking->statusLabel() }}</span>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
            <div class="space-y-6">
                <div class="card p-5">
                    <h2 class="text-sm font-semibold text-white">Seats</h2>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($booking->seats->sortBy('row') as $seat)
                            <span class="flex h-10 min-w-10 items-center justify-center rounded-lg border border-white/10 bg-white/[.03] px-2 text-xs font-bold text-white">
                                {{ $seat->label }}
                            </span>
                        @endforeach
                    </div>
                    <p class="mt-3 text-xs text-slate-500">
                        {{ $booking->seats->where('is_premium', true)->count() }} premium &middot;
                        {{ $booking->seats->where('is_premium', false)->count() }} standard
                    </p>
                </div>

                <div class="card p-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-white">Tickets</h2>
                        <span class="text-xs text-slate-500">{{ $booking->tickets->count() }} issued</span>
                    </div>

                    <div class="mt-3 space-y-2">
                        @forelse ($booking->tickets as $ticket)
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-white/[.07] bg-white/[.02] px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-600/15 text-xs font-bold text-brand-300">{{ $ticket->seat->label }}</span>
                                    <div>
                                        <p class="text-xs text-slate-300">Seat {{ $ticket->seat->label }}</p>
                                        <p class="text-[11px] uppercase tracking-wider {{ $ticket->status === 'valid' ? 'text-emerald-400' : 'text-slate-500' }}">
                                            {{ $ticket->status === 'used' ? 'Scanned' : ucfirst($ticket->status) }}
                                        </p>
                                    </div>
                                </div>

                                <a href="{{ route('tickets.show', $ticket->token) }}" class="text-xs font-medium text-brand-400 hover:text-brand-300">Open QR &rarr;</a>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Tickets are issued once payment is confirmed.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">
                <div class="card p-5">
                    <h2 class="text-sm font-semibold text-white">Payment</h2>

                    <dl class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between text-slate-400">
                            <dt>Total paid</dt>
                            <dd class="text-white">{{ PricingService::format($booking->total_amount_cents) }}</dd>
                        </div>
                        <div class="flex justify-between text-slate-400">
                            <dt>Method</dt>
                            <dd>Stripe</dd>
                        </div>
                        <div class="flex justify-between text-slate-400">
                            <dt>Status</dt>
                            <dd class="capitalize">{{ $booking->payment->status ?? 'pending' }}</dd>
                        </div>
                        <div class="flex justify-between text-slate-400">
                            <dt>Booked on</dt>
                            <dd>{{ $booking->created_at->format('j M Y') }}</dd>
                        </div>
                    </dl>

                    @if ($booking->isCancellable())
                        <form method="POST" action="{{ route('bookings.cancel', $booking) }}" class="mt-5"
                              onsubmit="return confirm('Cancel this booking? Your seats will be released.')">
                            @csrf
                            <button type="submit" class="btn w-full border border-red-500/25 bg-red-500/10 text-red-300 hover:bg-red-500/20">
                                Cancel booking
                            </button>
                        </form>
                        <p class="mt-2 text-center text-[11px] text-slate-500">
                            Cancellation allowed up to {{ \App\Services\SettingsService::get('cancel_cutoff_hours', 2) }} hours before showtime.
                        </p>
                    @elseif ($booking->isConfirmed())
                        <p class="mt-4 rounded-lg border border-white/[.07] bg-white/[.02] px-3 py-2 text-[11px] leading-relaxed text-slate-500">
                            This booking can no longer be cancelled online. Please contact the box office.
                        </p>
                    @endif
                </div>

                <a href="{{ route('movies.index') }}" class="btn btn-ghost w-full">Browse more movies</a>
            </aside>
        </div>
    </div>
</x-layouts.public>
