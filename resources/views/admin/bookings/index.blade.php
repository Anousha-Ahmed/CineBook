<x-layouts.admin heading="Bookings" subheading="{{ $bookings->total() }} bookings on record">
    <div class="space-y-5">
        <form method="GET" action="{{ route('admin.bookings.index') }}" class="grid gap-3 rounded-2xl border border-white/[.07] bg-night-900/60 p-4 sm:grid-cols-2 lg:grid-cols-[1.2fr_0.7fr_0.9fr_0.9fr_auto]">
            <input type="search" name="search" value="{{ request('search') }}" class="input" placeholder="Booking #, name or email">
            <select name="status" class="input">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
            <select name="movie" class="input">
                <option value="">All movies</option>
                @foreach ($movies as $movie)
                    <option value="{{ $movie->id }}" @selected((string) request('movie') === (string) $movie->id)>{{ $movie->title }}</option>
                @endforeach
            </select>
            <input type="date" name="date" value="{{ request('date') }}" class="input">
            <input type="search" name="customer" value="{{ request('customer') }}" class="input" placeholder="Customer email">
            <label class="flex items-center gap-2 text-xs text-slate-400 sm:col-span-2 lg:col-span-4">
                <input type="checkbox" name="refund_issues" value="1" @checked(request()->boolean('refund_issues')) class="h-4 w-4 rounded border-white/20 bg-night-800 text-brand-500 focus:ring-brand-500/40">
                Only bookings with pending or failed refunds
            </label>
            <button type="submit" class="btn btn-ghost">Filter</button>
        </form>

        <div class="table-shell">
            @if ($bookings->isEmpty())
                <div class="px-6 py-16 text-center">
                    <p class="font-display text-lg font-semibold text-white">No bookings found</p>
                    <p class="mt-1 text-sm text-slate-500">Try clearing the filters.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-white/[.02] text-[11px] uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3.5 font-medium">Booking</th>
                                <th class="px-5 py-3.5 font-medium">Customer</th>
                                <th class="px-5 py-3.5 font-medium">Screening</th>
                                <th class="px-5 py-3.5 font-medium">Tickets</th>
                                <th class="px-5 py-3.5 font-medium">Status</th>
                                <th class="px-5 py-3.5 font-medium text-right">Total</th>
                                <th class="px-5 py-3.5 text-right font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[.05]">
                            @foreach ($bookings as $booking)
                                <tr class="transition hover:bg-white/[.02]">
                                    <td class="px-5 py-3.5">
                                        <a href="{{ route('admin.bookings.show', $booking) }}" class="font-medium text-slate-200 transition hover:text-brand-300">#{{ $booking->id }}</a>
                                        <p class="text-xs text-slate-500">{{ $booking->created_at->format('j M Y, g:i A') }}</p>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <p class="text-slate-300">{{ $booking->user?->name ?? 'Deleted user' }}</p>
                                        <p class="text-xs text-slate-500">{{ $booking->user?->email }}</p>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <p class="text-slate-300">{{ $booking->showtime?->movie?->title }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ $booking->showtime?->start_time?->format('j M, g:i A') }}
                                            &middot; {{ $booking->showtime?->screen?->name }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-400">{{ $booking->tickets_count }}</td>
                                    <td class="px-5 py-3.5"><span class="{{ $booking->statusClasses() }}">{{ $booking->statusLabel() }}</span></td>
                                    <td class="px-5 py-3.5 text-right font-medium text-slate-200">{{ \App\Services\PricingService::format($booking->total_amount_cents) }}</td>
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.bookings.show', $booking) }}" class="rounded-lg border border-white/10 px-2.5 py-1.5 text-xs font-medium text-slate-300 transition hover:bg-white/[.04]">View</a>
                                            @if (in_array($booking->status, ['cancelled', 'refunded'], true))
                                                <span class="text-xs text-slate-600">—</span>
                                            @else
                                                <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}" onsubmit="return confirm('Cancel booking #{{ $booking->id }} and release its seats?')">
                                                    @csrf
                                                    <button type="submit" class="rounded-lg border border-amber-500/25 px-2.5 py-1.5 text-xs font-medium text-amber-300 transition hover:bg-amber-500/10">Cancel</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-white/[.06] px-5 py-4">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
