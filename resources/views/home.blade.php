@php
    use App\Services\PricingService;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;
@endphp

<x-layouts.public title="CineBook — Book cinema tickets in Downtown">
    @if ($heroMovie)
        <section class="relative isolate overflow-hidden">
            <div class="absolute inset-0 -z-10">
                @if ($heroMovie->backdrop_url || $heroMovie->poster_url)
                    <img src="{{ $heroMovie->backdrop_url ?: $heroMovie->poster_url }}" alt="" class="h-full w-full object-cover object-center" fetchpriority="high" aria-hidden="true">
                @endif
                <div class="absolute inset-0 bg-gradient-to-r from-night-950 via-night-950/80 to-night-950/15"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-night-950 via-night-950/10 to-night-950/30"></div>
            </div>
            <div class="pointer-events-none absolute -left-40 top-1/4 -z-10 h-96 w-96 rounded-full bg-brand-600/25 blur-3xl" aria-hidden="true"></div>

            <div class="mx-auto max-w-7xl px-4 pb-16 pt-20 sm:px-6 sm:pt-28 lg:px-8 lg:pb-24 lg:pt-36">
                <div class="max-w-2xl animate-fade-up">
                    <p class="eyebrow flex items-center gap-2.5">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand-400 opacity-70"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-brand-400"></span>
                        </span>
                        Now showing &middot; CineBook Downtown
                    </p>

                    <h1 class="mt-5 font-display text-5xl font-semibold leading-[1.02] tracking-tight text-white drop-shadow-[0_4px_30px_rgba(0,0,0,0.65)] text-balance sm:text-6xl lg:text-7xl">
                        {{ $heroMovie->title }}
                    </h1>

                    <div class="mt-6 h-px w-24 bg-gradient-to-r from-brand-500/80 to-transparent"></div>

                    <div class="mt-5 flex flex-wrap items-center gap-2">
                        <span class="chip border-brand-500/25 bg-brand-500/10 text-brand-300">{{ $heroMovie->rating }}</span>
                        <span class="chip">{{ $heroMovie->duration_minutes }} min</span>
                        @foreach ($heroMovie->genres->take(2) as $genre)
                            <span class="chip">{{ $genre->name }}</span>
                        @endforeach
                        @if ($heroMovie->next_showtime_at)
                            <span class="chip border-white/20 bg-white/[.07] text-white">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                Next show {{ Carbon::parse($heroMovie->next_showtime_at)->format('D, g:i A') }}
                            </span>
                        @endif
                    </div>

                    <p class="mt-6 max-w-xl text-sm leading-relaxed text-slate-300 sm:text-base">
                        {{ Str::limit($heroMovie->synopsis, 220) }}
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <a href="{{ route('movies.show', $heroMovie) }}" class="btn btn-primary">
                            Find tickets
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </a>
                        <a href="#whats-on" class="btn btn-ghost">Browse today's schedule</a>
                    </div>

                    <p class="mt-5 flex flex-wrap items-center gap-x-2.5 gap-y-1 text-xs text-slate-400">
                        <span>Standard {{ PricingService::format(1000) }}</span>
                        <span class="text-slate-600">·</span>
                        <span>Premium {{ PricingService::format(1000 + PricingService::premiumSurchargeCents()) }}</span>
                        <span class="text-slate-600">·</span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m4 12.5 5 5L20 6.5" /></svg>
                            Free cancellation up to 2 hours before showtime
                        </span>
                    </p>
                </div>
            </div>
        </section>
    @endif

    <section id="whats-on" class="mx-auto -mt-6 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="card overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-white/[.06] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                <div>
                    <h2 class="font-display text-xl font-semibold text-white">What's on at Downtown?</h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Screen 1 &amp; Screen 2 &middot;
                        Standard {{ PricingService::format(1000) }} &middot;
                        Premium {{ PricingService::format(1000 + PricingService::premiumSurchargeCents()) }}
                    </p>
                </div>
                <a href="{{ route('movies.index') }}" class="text-sm font-medium text-brand-400 transition hover:text-brand-300">All movies &rarr;</a>
            </div>

            <div class="px-5 py-5 sm:px-7">
                @if ($dates->isEmpty())
                    <p class="text-sm text-slate-500">No showtimes scheduled right now. Please check back soon.</p>
                @else
                    <div class="hide-scrollbar -mx-1 flex gap-2 overflow-x-auto px-1 pb-1">
                        @foreach ($dates as $date)
                            @php
                                $d = Carbon::parse($date);
                            @endphp
                            <a href="{{ route('home', ['date' => $date]) }}#whats-on"
                               class="flex min-w-[86px] flex-col items-center rounded-xl border px-4 py-2.5 transition {{ $selectedDate === $date ? 'border-brand-500 bg-brand-600 text-white shadow-lg shadow-brand-600/25' : 'border-white/10 bg-white/[.02] text-slate-300 hover:border-white/25' }}">
                                <span class="text-[10px] font-semibold uppercase tracking-widest {{ $selectedDate === $date ? 'text-white/80' : 'text-slate-500' }}">
                                    {{ $d->isToday() ? 'Today' : $d->format('D') }}
                                </span>
                                <span class="mt-0.5 font-display text-lg font-semibold leading-none">{{ $d->format('j') }}</span>
                                <span class="mt-0.5 text-[10px] uppercase tracking-wider {{ $selectedDate === $date ? 'text-white/70' : 'text-slate-500' }}">{{ $d->format('M') }}</span>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-6 space-y-3">
                        @forelse ($dayShowtimes as $showtimes)
                            @php
                                $movie = $showtimes->first()->movie;
                            @endphp
                            <div class="flex flex-col gap-4 rounded-xl border border-white/[.06] bg-white/[.02] p-4 sm:flex-row sm:items-center sm:gap-5">
                                <div class="h-16 w-12 shrink-0 overflow-hidden rounded-lg bg-night-800">
                                    @if ($movie->poster_url)
                                        <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}" class="h-full w-full object-cover">
                                    @endif
                                </div>

                                <div class="min-w-0 sm:w-64 sm:shrink-0">
                                    <a href="{{ route('movies.show', $movie) }}" class="block truncate font-medium text-white transition hover:text-brand-400">{{ $movie->title }}</a>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $showtimes->first()->screen->name }} &middot; {{ $movie->rating }} &middot; {{ $movie->duration_minutes }} min
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    @foreach ($showtimes as $showtime)
                                        @php
                                            $available = max(0, ($seatCounts[$showtime->screen_id] ?? 0) - ($takenByShowtime[$showtime->id] ?? 0));
                                        @endphp
                                        @if ($available > 0)
                                            <a href="{{ route('booking.seats', $showtime) }}"
                                               class="group flex flex-col items-center rounded-lg border border-white/10 px-3.5 py-1.5 transition hover:border-brand-500 hover:bg-brand-600/10">
                                                <span class="text-sm font-semibold text-white">{{ $showtime->start_time->format('g:i A') }}</span>
                                                <span class="text-[10px] text-slate-500 group-hover:text-brand-300">{{ $available }} seats</span>
                                            </a>
                                        @else
                                            <span class="flex flex-col items-center rounded-lg border border-white/[.05] px-3.5 py-1.5 text-slate-600">
                                                <span class="text-sm font-semibold line-through">{{ $showtime->start_time->format('g:i A') }}</span>
                                                <span class="text-[10px]">Sold out</span>
                                            </span>
                                        @endif
                                    @endforeach

                                    <a href="{{ route('movies.show', $movie) }}" class="flex items-center px-2 text-xs font-medium text-slate-500 transition hover:text-white">All times &rarr;</a>
                                </div>
                            </div>
                        @empty
                            <p class="py-6 text-center text-sm text-slate-500">Nothing scheduled for this day.</p>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="mx-auto mt-16 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="eyebrow">In cinemas</p>
                <h2 class="section-title mt-2">Now showing</h2>
            </div>
            <a href="{{ route('movies.index', ['filter' => 'now-showing']) }}" class="hidden text-sm font-medium text-slate-400 transition hover:text-white sm:block">Explore all movies &rarr;</a>
        </div>

        <div class="mt-7 grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-5 lg:grid-cols-5">
            @foreach ($nowShowing->take(10) as $movie)
                <x-movie-card :movie="$movie" />
            @endforeach
        </div>

        @if ($nowShowing->isEmpty())
            <p class="mt-6 text-sm text-slate-500">No movies are on sale right now.</p>
        @endif
    </section>

    @if ($comingSoon->isNotEmpty())
        <section class="mx-auto mt-16 max-w-7xl px-4 sm:px-6 lg:px-8">
            <div>
                <p class="eyebrow">On the horizon</p>
                <h2 class="section-title mt-2">Coming soon</h2>
                <p class="muted mt-2">New arrivals over the next few weeks. Tickets go on sale closer to release.</p>
            </div>

            <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($comingSoon as $movie)
                    <a href="{{ route('movies.show', $movie) }}" class="card card-hover flex gap-4 p-3">
                        <div class="h-28 w-20 shrink-0 overflow-hidden rounded-lg bg-night-800">
                            @if ($movie->poster_url)
                                <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}" class="h-full w-full object-cover">
                            @endif
                        </div>
                        <div class="min-w-0 py-1">
                            <h3 class="truncate font-display text-sm font-semibold text-white">{{ $movie->title }}</h3>
                            <p class="mt-1 line-clamp-2 text-xs leading-relaxed text-slate-500">{{ $movie->synopsis }}</p>
                            <p class="mt-2 text-[11px] text-slate-500">{{ $movie->rating }} &middot; {{ $movie->duration_minutes }} min</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.public>
