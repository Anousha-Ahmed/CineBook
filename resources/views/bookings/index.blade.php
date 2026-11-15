@php
    use App\Services\PricingService;
@endphp

<x-layouts.public title="My bookings — CineBook">
    <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="eyebrow">Your account</p>
                <h1 class="mt-2 font-display text-2xl font-semibold tracking-tight text-white sm:text-3xl">My bookings</h1>
                <p class="mt-2 text-sm text-slate-400">Tickets, showtimes, and cancellations — all in one place.</p>
            </div>

            <a href="{{ route('movies.index') }}" class="btn btn-primary btn-sm">Book another show</a>
        </div>

        <div class="mt-7 inline-flex rounded-xl border border-white/10 bg-white/[.02] p-1">
            @foreach (['upcoming' => 'Upcoming', 'past' => 'Past & cancelled'] as $value => $label)
                <a href="{{ route('bookings.index', ['tab' => $value]) }}"
                   class="rounded-lg px-4 py-2 text-sm font-medium transition {{ $tab === $value ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/25' : 'text-slate-400 hover:text-white' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if ($bookings->isEmpty())
            <div class="card mt-6 flex flex-col items-center px-6 py-16 text-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/[.04] text-slate-500">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4V8Z" /><path d="M12 6v12" stroke-dasharray="2 3" /></svg>
                </div>
                <h2 class="mt-4 font-display text-lg font-semibold text-white">
                    {{ $tab === 'upcoming' ? 'No upcoming bookings' : 'Nothing here yet' }}
                </h2>
                <p class="mt-1 max-w-sm text-sm text-slate-500">
                    {{ $tab === 'upcoming' ? 'Pick a film, choose your seats, and your ticket will appear here.' : 'Past and cancelled bookings will show up here.' }}
                </p>
                <a href="{{ route('movies.index') }}" class="btn btn-primary btn-sm mt-6">Browse movies</a>
            </div>
        @else
            <div class="mt-6 space-y-3">
                @foreach ($bookings as $booking)
                    @php
                        $showtime = $booking->showtime;
                    @endphp
                    <div class="card card-hover flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:gap-5 sm:p-5">
                        <a href="{{ route('bookings.show', $booking) }}" class="block h-28 w-20 shrink-0 overflow-hidden rounded-lg bg-night-800">
                            @if ($showtime->movie->poster_url)
                                <img src="{{ $showtime->movie->poster_url }}" alt="" class="h-full w-full object-cover">
                            @endif
                        </a>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('bookings.show', $booking) }}" class="font-display text-base font-semibold text-white transition hover:text-brand-400">
                                    {{ $showtime->movie->title }}
                                </a>
                                <span class="{{ $booking->statusClasses() }}">{{ $booking->statusLabel() }}</span>
                            </div>

                            <p class="mt-1.5 text-xs text-slate-400">
                                {{ $showtime->start_time->format('D j M Y, g:i A') }} &middot; {{ $showtime->screen->name }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">
                                Order #{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }} &middot;
                                {{ $booking->seats->count() }} {{ \Illuminate\Support\Str::plural('seat', $booking->seats->count()) }} &middot;
                                {{ $booking->seats->sortBy('row')->map(fn ($s) => $s->label)->implode(', ') }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between gap-4 sm:flex-col sm:items-end">
                            <span class="font-display text-lg font-semibold text-white">{{ PricingService::format($booking->total_amount_cents) }}</span>

                            <div class="flex items-center gap-2">
                                @if ($booking->isConfirmed() && $booking->tickets->isNotEmpty())
                                    <a href="{{ route('tickets.show', $booking->tickets->first()->token) }}" class="btn btn-ghost btn-sm">View ticket</a>
                                @else
                                    <a href="{{ route('bookings.show', $booking) }}" class="btn btn-ghost btn-sm">Details</a>
                                @endif

                                @if ($booking->isCancellable())
                                    <form method="POST" action="{{ route('bookings.cancel', $booking) }}"
                                          onsubmit="return confirm('Cancel this booking? Your seats will be released.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm border border-red-500/25 bg-red-500/10 text-red-300 hover:bg-red-500/20">Cancel</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{ $bookings->links() }}
        @endif
    </div>
</x-layouts.public>
