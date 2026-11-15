<x-layouts.admin heading="Dashboard" subheading="{{ now()->format('l, j F Y') }}">
    <x-slot name="actions">
        <a href="{{ route('admin.showtimes.create') }}" class="btn btn-primary text-xs">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14" /></svg>
            New showtime
        </a>
    </x-slot>

    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @php
                $cards = [
                    ['label' => 'Revenue today', 'value' => \App\Services\PricingService::format($revenueCents), 'hint' => 'Successful payments'],
                    ['label' => 'Bookings today', 'value' => number_format($bookingsToday), 'hint' => $confirmedToday.' confirmed'],
                    ['label' => 'Tickets sold', 'value' => number_format($ticketsSold), 'hint' => 'All confirmed bookings'],
                    ['label' => 'Occupancy today', 'value' => $occupancy.'%', 'hint' => 'Seats sold vs capacity'],
                ];
            @endphp

            @foreach ($cards as $card)
                <div class="card p-5">
                    <p class="text-xs font-medium uppercase tracking-[.16em] text-slate-500">{{ $card['label'] }}</p>
                    <p class="mt-3 font-display text-2xl font-semibold text-white">{{ $card['value'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $card['hint'] }}</p>
                </div>
            @endforeach
        </div>

        @if ($refundIssues > 0 || $failedJobs > 0)
            <div class="card flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
                    @if ($refundIssues > 0)
                        <span class="text-amber-300">
                            {{ $refundIssues }} refund{{ $refundIssues === 1 ? '' : 's' }} pending or failed
                            @if ($failedRefunds > 0)
                                <span class="text-red-300">({{ $failedRefunds }} failed)</span>
                            @endif
                        </span>
                    @endif
                    @if ($failedJobs > 0)
                        <span class="text-red-300">{{ $failedJobs }} failed queued job{{ $failedJobs === 1 ? '' : 's' }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    @if ($refundIssues > 0)
                        <a href="{{ route('admin.bookings.index', ['refund_issues' => 1]) }}" class="text-xs font-medium text-brand-400 hover:text-brand-300">Review refunds</a>
                    @endif
                    @if ($failedJobs > 0)
                        <a href="{{ route('admin.failed-jobs.index') }}" class="text-xs font-medium text-brand-400 hover:text-brand-300">Failed jobs</a>
                    @endif
                </div>
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
            <div class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-white/[.06] px-5 py-4">
                    <div>
                        <h2 class="font-display text-base font-semibold text-white">Recent bookings</h2>
                        <p class="text-xs text-slate-500">Latest activity across both screens</p>
                    </div>
                    <a href="{{ route('admin.bookings.index') }}" class="text-xs font-medium text-brand-400 hover:text-brand-300">View all</a>
                </div>

                @if ($recentBookings->isEmpty())
                    <p class="px-5 py-10 text-center text-sm text-slate-500">No bookings yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-white/[.02] text-[11px] uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-5 py-3 font-medium">Booking</th>
                                    <th class="px-5 py-3 font-medium">Movie</th>
                                    <th class="px-5 py-3 font-medium">Status</th>
                                    <th class="px-5 py-3 font-medium text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[.05]">
                                @foreach ($recentBookings as $booking)
                                    <tr>
                                        <td class="px-5 py-3">
                                            <p class="font-medium text-slate-200">#{{ $booking->id }}</p>
                                            <p class="text-xs text-slate-500">{{ $booking->user?->name }}</p>
                                        </td>
                                        <td class="px-5 py-3">
                                            <p class="text-slate-300">{{ $booking->showtime?->movie?->title }}</p>
                                            <p class="text-xs text-slate-500">
                                                {{ $booking->showtime?->start_time?->format('j M, g:i A') }}
                                                &middot; {{ $booking->showtime?->screen?->name }}
                                            </p>
                                        </td>
                                        <td class="px-5 py-3"><span class="{{ $booking->statusClasses() }}">{{ $booking->statusLabel() }}</span></td>
                                        <td class="px-5 py-3 text-right font-medium text-slate-200">{{ \App\Services\PricingService::format($booking->total_amount_cents) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-white/[.06] px-5 py-4">
                    <h2 class="font-display text-base font-semibold text-white">Today's showtimes</h2>
                    <a href="{{ route('admin.showtimes.index') }}" class="text-xs font-medium text-brand-400 hover:text-brand-300">Manage</a>
                </div>

                @if ($todayShowtimes->isEmpty())
                    <p class="px-5 py-10 text-center text-sm text-slate-500">Nothing scheduled for today.</p>
                @else
                    <ul class="divide-y divide-white/[.05]">
                        @foreach ($todayShowtimes as $showtime)
                            @php
                                $sold = (int) ($soldByShowtime[$showtime->id] ?? 0);
                                $capacity = max(1, \App\Models\Seat::where('screen_id', $showtime->screen_id)->count());
                                $fill = min(100, round(($sold / $capacity) * 100));
                            @endphp
                            <li class="px-5 py-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-slate-200">{{ $showtime->movie?->title }}</p>
                                        <p class="text-xs text-slate-500">{{ $showtime->start_time->format('g:i A') }} &middot; {{ $showtime->screen?->name }}</p>
                                    </div>
                                    <span class="text-xs text-slate-400">{{ $sold }}/{{ $capacity }}</span>
                                </div>
                                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-white/[.06]">
                                    <div class="h-full rounded-full bg-brand-500" style="width: {{ $fill }}%"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin>
