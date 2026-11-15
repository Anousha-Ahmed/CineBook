@php
    use App\Services\PricingService;
@endphp

<x-layouts.public title="Payment — CineBook">
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        @if (! $booking)
            <div class="card mx-auto max-w-md p-8 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white/[.04] text-slate-500">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9" /><path d="M12 8v5m0 3.5v.5" stroke-linecap="round" /></svg>
                </div>
                <h1 class="mt-5 font-display text-xl font-semibold text-white">No booking found</h1>
                <p class="mt-2 text-sm text-slate-400">We could not locate a recent booking for this payment.</p>
                <a href="{{ route('movies.index') }}" class="btn btn-primary mt-6">Browse movies</a>
            </div>
        @else
            <div class="text-center">
                @if ($paid && $booking->isConfirmed())
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-500/15 ring-1 ring-inset ring-emerald-500/30">
                        <svg class="h-8 w-8 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m5 13 4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </div>
                    <p class="eyebrow mt-6 text-emerald-400">Payment successful</p>
                    <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-white">You're booked in</h1>
                    <p class="mx-auto mt-3 max-w-md text-sm text-slate-400">
                        Order <span class="font-semibold text-white">#{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</span> is confirmed.
                        Show the QR code below at the door.
                    </p>
                @elseif ($processing)
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-500/15 ring-1 ring-inset ring-amber-500/30">
                        <svg class="h-8 w-8 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" stroke-linecap="round" /></svg>
                    </div>
                    <p class="eyebrow mt-6 text-amber-400">Payment processing</p>
                    <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-white">Almost there</h1>
                    <p class="mx-auto mt-3 max-w-md text-sm text-slate-400">
                        We're waiting for Stripe to confirm this payment. This page refreshes automatically,
                        and your tickets appear as soon as the confirmation arrives.
                    </p>
                    <meta http-equiv="refresh" content="5">
                @else
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-red-500/15 ring-1 ring-inset ring-red-500/30">
                        <svg class="h-8 w-8 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><circle cx="12" cy="12" r="9" /><path d="m9 9 6 6m0-6-6 6" stroke-linecap="round" /></svg>
                    </div>
                    <p class="eyebrow mt-6 text-red-400">Payment not completed</p>
                    <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-white">No tickets issued</h1>
                    <p class="mx-auto mt-3 max-w-md text-sm text-slate-400">
                        This payment did not complete, so no tickets were issued and no seats were kept.
                        You can pick your seats again whenever you're ready.
                    </p>
                @endif
            </div>

            <div class="card mt-8 overflow-hidden">
                <div class="flex flex-col gap-4 border-b border-white/[.06] p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="h-20 w-14 shrink-0 overflow-hidden rounded-lg bg-night-800">
                            @if ($booking->showtime->movie->poster_url)
                                <img src="{{ $booking->showtime->movie->poster_url }}" alt="" class="h-full w-full object-cover">
                            @endif
                        </div>
                        <div>
                            <h2 class="font-display text-lg font-semibold text-white">{{ $booking->showtime->movie->title }}</h2>
                            <p class="mt-1 text-xs text-slate-400">
                                {{ $booking->showtime->start_time->format('D j M Y, g:i A') }} &middot; {{ $booking->showtime->screen->name }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $booking->seats->count() }} {{ \Illuminate\Support\Str::plural('seat', $booking->seats->count()) }} &middot;
                                {{ PricingService::format($booking->total_amount_cents) }}
                            </p>
                        </div>
                    </div>

                    <span class="{{ $booking->statusClasses() }}">{{ $booking->statusLabel() }}</span>
                </div>

                @if ($booking->tickets->isNotEmpty())
                    <div class="grid gap-4 p-5 sm:grid-cols-2">
                        @foreach ($booking->tickets as $ticket)
                            <div class="flex items-center gap-4 rounded-xl border border-white/[.08] bg-white/[.02] p-3">
                                <div class="shrink-0 rounded-lg bg-white p-1.5">
                                    <div class="qr-code" data-url="{{ $ticket->token }}" data-size="96"></div>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs uppercase tracking-widest text-slate-500">Seat</p>
                                    <p class="font-display text-xl font-semibold text-white">{{ $ticket->seat->label }}</p>
                                    <a href="{{ route('tickets.show', $ticket->token) }}" class="mt-1 inline-block text-[11px] font-medium text-brand-400 hover:text-brand-300">Open ticket &rarr;</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('bookings.index') }}" class="btn btn-primary">My bookings</a>
                <a href="{{ route('home') }}" class="btn btn-ghost">Back to home</a>
            </div>
        @endif
    </div>

    @push('scripts')
        <script src="{{ asset('js/qrcode.min.js') }}" defer></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const nodes = document.querySelectorAll('.qr-code');
                if (!window.QRCode || !nodes.length) return;

                nodes.forEach((node) => {
                    new QRCode(node, {
                        text: node.dataset.url,
                        width: Number(node.dataset.size),
                        height: Number(node.dataset.size),
                        colorDark: '#0b1120',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.M,
                    });
                });
            });
        </script>
    @endpush
</x-layouts.public>
