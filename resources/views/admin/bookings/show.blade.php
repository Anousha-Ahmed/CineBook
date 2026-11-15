<x-layouts.admin heading="Booking #{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}" subheading="{{ $booking->showtime->movie->title }} &middot; {{ $booking->showtime->start_time->format('D j M Y, g:i A') }}">
    <x-slot name="actions">
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-ghost text-xs">Back to bookings</a>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="space-y-6">
            <div class="card p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="font-display text-base font-semibold text-white">Showtime</h2>
                        <p class="mt-1 text-sm text-slate-400">{{ $booking->showtime->movie->title }}</p>
                    </div>
                    <span class="{{ $booking->statusClasses() }}">{{ $booking->statusLabel() }}</span>
                </div>

                <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-slate-500">Screen</dt>
                        <dd class="mt-1 text-slate-200">{{ $booking->showtime->screen->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-slate-500">Starts</dt>
                        <dd class="mt-1 text-slate-200">{{ $booking->showtime->start_time->format('l, j M Y, g:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-slate-500">Seats</dt>
                        <dd class="mt-1 text-slate-200">{{ $booking->seats->sortBy('row')->map->label->implode(', ') ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-slate-500">Booked</dt>
                        <dd class="mt-1 text-slate-200">{{ $booking->created_at->format('j M Y, g:i A') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="card overflow-hidden">
                <div class="border-b border-white/[.06] px-6 py-4">
                    <h2 class="font-display text-base font-semibold text-white">Tickets</h2>
                </div>

                @if ($booking->tickets->isEmpty())
                    <p class="px-6 py-8 text-center text-sm text-slate-500">No tickets issued for this booking.</p>
                @else
                    <table class="w-full text-left text-sm">
                        <thead class="bg-white/[.02] text-[11px] uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-6 py-3 font-medium">Seat</th>
                                <th class="px-6 py-3 font-medium">Status</th>
                                <th class="px-6 py-3 font-medium">Scanned at</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[.05]">
                            @foreach ($booking->tickets as $ticket)
                                <tr>
                                    <td class="px-6 py-3 font-medium text-slate-200">{{ $ticket->seat->label }}</td>
                                    <td class="px-6 py-3">
                                        <span class="badge {{ $ticket->status === 'valid' ? 'badge-green' : ($ticket->status === 'used' ? 'badge-blue' : 'badge-slate') }}">
                                            {{ ucfirst($ticket->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-xs text-slate-500">{{ $ticket->used_at?->format('j M, g:i A') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="card overflow-hidden">
                <div class="border-b border-white/[.06] px-6 py-4">
                    <h2 class="font-display text-base font-semibold text-white">Scan history</h2>
                </div>

                @if ($scans->isEmpty())
                    <p class="px-6 py-8 text-center text-sm text-slate-500">No scan attempts recorded.</p>
                @else
                    <table class="w-full text-left text-sm">
                        <thead class="bg-white/[.02] text-[11px] uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-6 py-3 font-medium">Result</th>
                                <th class="px-6 py-3 font-medium">By</th>
                                <th class="px-6 py-3 font-medium">When</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[.05]">
                            @foreach ($scans as $scan)
                                <tr>
                                    <td class="px-6 py-3 text-slate-300">{{ ucfirst(str_replace('_', ' ', $scan->result)) }}</td>
                                    <td class="px-6 py-3 text-slate-400">{{ $scan->staff?->name ?? '—' }}</td>
                                    <td class="px-6 py-3 text-xs text-slate-500">{{ $scan->created_at->format('j M, g:i A') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <aside class="space-y-4">
            <div class="card p-6">
                <h2 class="font-display text-base font-semibold text-white">Customer</h2>
                <p class="mt-3 text-sm font-medium text-slate-200">{{ $booking->user?->name ?? 'Deleted user' }}</p>
                <p class="text-xs text-slate-500">{{ $booking->user?->email }}</p>
            </div>

            <div class="card p-6">
                <h2 class="font-display text-base font-semibold text-white">Payment</h2>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between text-slate-400">
                        <dt>Total</dt>
                        <dd class="text-white">{{ \App\Services\PricingService::format($booking->total_amount_cents) }}</dd>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <dt>Status</dt>
                        <dd class="capitalize text-slate-200">{{ $booking->payment?->status ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <dt>Refund</dt>
                        <dd class="capitalize {{ $booking->payment?->refund_status === 'failed' ? 'text-red-300' : 'text-slate-200' }}">
                            {{ $booking->payment?->refund_status ?? 'none' }}
                        </dd>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <dt>Attempts</dt>
                        <dd class="text-slate-200">{{ $booking->payment?->refund_attempts ?? 0 }}</dd>
                    </div>
                </dl>

                @if ($booking->payment?->failure_reason)
                    <p class="mt-3 rounded-lg border border-red-500/20 bg-red-500/10 px-3 py-2 text-[11px] leading-relaxed text-red-200">
                        {{ $booking->payment->failure_reason }}
                    </p>
                @endif
            </div>

            @if (! in_array($booking->status, ['cancelled', 'refunded'], true))
                <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}" class="card p-6"
                      onsubmit="return confirm('Cancel this booking, release its seats and refund the payment?')">
                    @csrf
                    <h2 class="font-display text-base font-semibold text-white">Cancel booking</h2>
                    <p class="mt-2 text-xs leading-relaxed text-slate-500">
                        Seats are released, tickets are voided and the payment is refunded through the payment gateway.
                    </p>
                    <button type="submit" class="btn mt-4 w-full border border-red-500/25 bg-red-500/10 text-red-300 hover:bg-red-500/20">Cancel &amp; refund</button>
                </form>
            @endif
        </aside>
    </div>
</x-layouts.admin>
