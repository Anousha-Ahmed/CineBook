<x-layouts.admin heading="Showtimes" subheading="{{ $showtimes->total() }} scheduled slots">
    <x-slot name="actions">
        <a href="{{ route('admin.showtimes.create') }}" class="btn btn-primary text-xs">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14" /></svg>
            New showtime
        </a>
    </x-slot>

    <div class="space-y-5">
        <form method="GET" action="{{ route('admin.showtimes.index') }}" class="grid gap-3 rounded-2xl border border-white/[.07] bg-night-900/60 p-4 sm:grid-cols-[1fr_1fr_auto_auto]">
            <select name="movie" class="input">
                <option value="">All movies</option>
                @foreach ($movies as $movie)
                    <option value="{{ $movie->id }}" @selected(request('movie') == $movie->id)>{{ $movie->title }}</option>
                @endforeach
            </select>
            <input type="date" name="date" value="{{ request('date') }}" class="input">
            <label class="flex cursor-pointer items-center gap-2 px-2 text-sm text-slate-400">
                <input type="checkbox" name="when" value="upcoming" @checked(request('when') === 'upcoming') class="h-4 w-4 rounded border-white/20 bg-night-800 text-brand-500 focus:ring-brand-500/40">
                Upcoming only
            </label>
            <button type="submit" class="btn btn-ghost">Filter</button>
        </form>

        <div class="table-shell">
            @if ($showtimes->isEmpty())
                <div class="px-6 py-16 text-center">
                    <p class="font-display text-lg font-semibold text-white">No showtimes found</p>
                    <p class="mt-1 text-sm text-slate-500">Adjust the filters or schedule a new slot.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-white/[.02] text-[11px] uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3.5 font-medium">Movie</th>
                                <th class="px-5 py-3.5 font-medium">Screen</th>
                                <th class="px-5 py-3.5 font-medium">Starts</th>
                                <th class="px-5 py-3.5 font-medium">Base price</th>
                                <th class="px-5 py-3.5 font-medium">Bookings</th>
                                <th class="px-5 py-3.5 font-medium">Status</th>
                                <th class="px-5 py-3.5 text-right font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[.05]">
                            @foreach ($showtimes as $showtime)
                                <tr class="transition hover:bg-white/[.02]">
                                    <td class="px-5 py-3.5 font-medium text-slate-200">{{ $showtime->movie?->title }}</td>
                                    <td class="px-5 py-3.5 text-slate-400">{{ $showtime->screen?->name }}</td>
                                    <td class="px-5 py-3.5">
                                        <p class="text-slate-200">{{ $showtime->start_time->format('D j M') }}</p>
                                        <p class="text-xs text-slate-500">{{ $showtime->start_time->format('g:i A') }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-400">{{ \App\Services\PricingService::format($showtime->base_price_cents) }}</td>
                                    <td class="px-5 py-3.5 text-slate-400">{{ $showtime->bookings_count }}</td>
                                    <td class="px-5 py-3.5">
                                        @if ($showtime->status === 'scheduled')
                                            <span class="badge badge-green">Scheduled</span>
                                        @else
                                            <span class="badge badge-red">Cancelled</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.showtimes.edit', $showtime) }}" class="rounded-lg border border-white/10 px-2.5 py-1.5 text-xs font-medium text-slate-300 transition hover:border-white/25 hover:text-white">Edit</a>
                                            @if ($showtime->status === 'scheduled')
                                                <form method="POST" action="{{ route('admin.showtimes.cancel', $showtime) }}" onsubmit="return confirm('Cancel this showtime? Every booking will be refunded and customers will be emailed.')">
                                                    @csrf
                                                    <button type="submit" class="rounded-lg border border-amber-500/25 px-2.5 py-1.5 text-xs font-medium text-amber-300 transition hover:bg-amber-500/10">Cancel</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('admin.showtimes.destroy', $showtime) }}" onsubmit="return confirm('Delete this showtime?')">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="rounded-lg border border-red-500/25 px-2.5 py-1.5 text-xs font-medium text-red-300 transition hover:bg-red-500/10">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-white/[.06] px-5 py-4">
                    {{ $showtimes->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
