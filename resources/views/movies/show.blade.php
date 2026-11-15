@php
    use App\Services\PricingService;
    use Illuminate\Support\Carbon;
@endphp

<x-layouts.public :title="$movie->title . ' — CineBook'">
    <div class="relative isolate overflow-hidden border-b border-white/[.06]">
        <div class="absolute inset-0 -z-10">
            @if ($movie->poster_url)
                <img src="{{ $movie->poster_url }}" alt="" class="h-full w-full object-cover object-top opacity-25" aria-hidden="true">
            @endif
            <div class="absolute inset-0 bg-gradient-to-r from-night-950 via-night-950/90 to-night-950/60"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-night-950 to-transparent"></div>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
            <a href="{{ route('movies.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-400 transition hover:text-white">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m6 6-6-6 6-6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                All movies
            </a>

            <div class="mt-6 grid gap-8 lg:grid-cols-[16rem_minmax(0,1fr)] lg:gap-12">
                <div class="mx-auto w-48 sm:w-56 lg:mx-0 lg:w-full">
                    <div class="aspect-[2/3] overflow-hidden rounded-2xl border border-white/10 bg-night-800 shadow-2xl">
                        @if ($movie->poster_url)
                            <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }} poster" class="h-full w-full object-cover">
                        @endif
                    </div>
                </div>

                <div class="max-w-2xl">
                    <p class="eyebrow">At CineBook Downtown</p>
                    <h1 class="mt-3 font-display text-3xl font-semibold tracking-tight text-white text-balance sm:text-4xl lg:text-5xl">{{ $movie->title }}</h1>

                    <div class="mt-5 flex flex-wrap items-center gap-2">
                        <span class="chip border-brand-500/25 bg-brand-500/10 text-brand-300">{{ $movie->rating }}</span>
                        <span class="chip">{{ $movie->duration_minutes }} min</span>
                        @foreach ($movie->genres as $genre)
                            <span class="chip">{{ $genre->name }}</span>
                        @endforeach
                    </div>

                    <p class="mt-6 text-sm leading-relaxed text-slate-300 sm:text-base">{{ $movie->synopsis }}</p>

                    <dl class="mt-8 grid max-w-md grid-cols-2 gap-4 border-t border-white/[.07] pt-6 text-sm">
                        <div>
                            <dt class="text-xs uppercase tracking-widest text-slate-500">Standard seat</dt>
                            <dd class="mt-1 font-display text-lg font-semibold text-white">{{ PricingService::format(1000) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-widest text-slate-500">Premium seat</dt>
                            <dd class="mt-1 font-display text-lg font-semibold text-white">{{ PricingService::format(1000 + PricingService::premiumSurchargeCents()) }}</dd>
                        </div>
                    </dl>

                    <div class="mt-8 flex flex-wrap gap-3">
                        @if ($byDate->isNotEmpty())
                            <a href="#showtimes" class="btn btn-primary">
                                Choose a showtime
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m7-7H5" stroke-linecap="round" /></svg>
                            </a>
                        @else
                            <span class="chip border-amber-500/25 bg-amber-500/10 text-amber-300">Tickets not on sale yet</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="showtimes" class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="eyebrow">Book your seat</p>
                <h2 class="section-title mt-2">Showtimes</h2>
            </div>
            <p class="hidden text-xs text-slate-500 sm:block">Up to {{ \App\Services\SettingsService::get('max_seats', 6) }} seats per booking</p>
        </div>

        @if ($byDate->isEmpty())
            <div class="card mt-6 flex flex-col items-center px-6 py-14 text-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/[.04] text-slate-500">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" stroke-linecap="round" /></svg>
                </div>
                <h3 class="mt-4 font-display text-lg font-semibold text-white">No showtimes yet</h3>
                <p class="mt-1 max-w-sm text-sm text-slate-500">This title is coming soon. Check back closer to release.</p>
                <a href="{{ route('movies.index', ['filter' => 'now-showing']) }}" class="btn btn-ghost btn-sm mt-6">Browse now showing</a>
            </div>
        @else
            <div class="mt-6 space-y-4">
                @foreach ($byDate as $date => $showtimes)
                    @php
                        $d = Carbon::parse($date);
                    @endphp
                    <div class="card overflow-hidden">
                        <div class="flex items-center justify-between gap-3 border-b border-white/[.06] px-5 py-3.5">
                            <div class="flex items-baseline gap-2">
                                <h3 class="font-display text-sm font-semibold text-white">
                                    {{ $d->isToday() ? 'Today' : ($d->isTomorrow() ? 'Tomorrow' : $d->format('l')) }}
                                </h3>
                                <span class="text-xs text-slate-500">{{ $d->format('j M Y') }}</span>
                            </div>
                            <span class="text-[11px] uppercase tracking-widest text-slate-500">{{ $showtimes->count() }} shows</span>
                        </div>

                        <div class="flex flex-wrap gap-2.5 px-5 py-4">
                            @foreach ($showtimes as $showtime)
                                @php
                                    $available = max(0, ($seatCounts[$showtime->screen_id] ?? 0) - ($taken[$showtime->id] ?? 0));
                                @endphp
                                @if ($available > 0)
                                    <a href="{{ route('booking.seats', $showtime) }}"
                                       class="group flex min-w-[7.5rem] flex-col rounded-xl border border-white/10 bg-white/[.02] px-4 py-2.5 transition hover:border-brand-500 hover:bg-brand-600/10">
                                        <span class="text-sm font-semibold text-white">{{ $showtime->start_time->format('g:i A') }}</span>
                                        <span class="mt-0.5 text-[11px] text-slate-500 group-hover:text-brand-300">{{ $showtime->screen->name }} &middot; {{ $available }} left</span>
                                    </a>
                                @else
                                    <span class="flex min-w-[7.5rem] flex-col rounded-xl border border-white/[.05] px-4 py-2.5 text-slate-600">
                                        <span class="text-sm font-semibold line-through">{{ $showtime->start_time->format('g:i A') }}</span>
                                        <span class="mt-0.5 text-[11px]">Sold out</span>
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($related->isNotEmpty())
            <div class="mt-16">
                <p class="eyebrow">You might also like</p>
                <h2 class="section-title mt-2">More at Downtown</h2>

                <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-5 lg:grid-cols-5">
                    @foreach ($related as $other)
                        <x-movie-card :movie="$other" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.public>
