@php
    $filters = [
        'all' => 'All movies',
        'now-showing' => 'Now showing',
        'coming-soon' => 'Coming soon',
    ];
@endphp

<x-layouts.public title="Movies — CineBook">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="pt-12 sm:pt-16">
            <p class="eyebrow">Movies &middot; Cinema Downtown</p>
            <h1 class="mt-3 font-display text-3xl font-semibold tracking-tight text-white sm:text-4xl">Find your next escape</h1>
            <p class="muted mt-3 max-w-xl">Discover what's playing and what's next. All showtimes are local to Downtown.</p>
        </div>

        <form method="GET" action="{{ route('movies.index') }}" class="card mt-8 p-4 sm:p-5">
            <div class="grid gap-4 lg:grid-cols-[1fr_14rem_auto]">
                <div>
                    <label for="search" class="label">Search movies</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7" /><path d="m20 20-3.2-3.2" stroke-linecap="round" /></svg>
                        <input id="search" name="search" type="search" value="{{ $search }}" placeholder="Search by title or story..."
                               class="input pl-10">
                    </div>
                </div>

                <div>
                    <label for="genre" class="label">Genre</label>
                    <select id="genre" name="genre" class="input">
                        <option value="">All genres</option>
                        @foreach ($genres as $genre)
                            <option value="{{ $genre->id }}" @selected((string) $genreId === (string) $genre->id)>{{ $genre->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="btn btn-primary w-full lg:w-auto">Apply filters</button>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-white/[.06] pt-4">
                @foreach ($filters as $value => $label)
                    @php
                        $isActive = ($filter ?: 'all') === $value;
                    @endphp
                    <a href="{{ route('movies.index', array_filter(['filter' => $value === 'all' ? null : $value, 'search' => $search, 'genre' => $genreId])) }}"
                       class="rounded-full px-3.5 py-1.5 text-xs font-semibold transition {{ $isActive ? 'bg-brand-600 text-white' : 'border border-white/10 text-slate-400 hover:border-white/25 hover:text-white' }}">
                        {{ $label }}
                    </a>
                @endforeach

                @if ($search || $genreId || $filter)
                    <a href="{{ route('movies.index') }}" class="ml-auto text-xs font-medium text-slate-500 transition hover:text-white">Clear all</a>
                @endif
            </div>
        </form>

        <div class="mt-8 flex items-center justify-between gap-4">
            <p class="text-sm text-slate-400">
                {{ $movies->total() }} {{ \Illuminate\Support\Str::plural('movie', $movies->total()) }}
                @if ($search)
                    matching &ldquo;{{ $search }}&rdquo;
                @endif
            </p>
            <p class="hidden text-xs text-slate-500 sm:block">Standard {{ \App\Services\PricingService::format(1000) }} &middot; Premium {{ \App\Services\PricingService::format(1000 + \App\Services\PricingService::premiumSurchargeCents()) }}</p>
        </div>

        @if ($movies->isEmpty())
            <div class="card mt-6 flex flex-col items-center px-6 py-16 text-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/[.04] text-slate-500">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="11" cy="11" r="7" /><path d="m20 20-3.2-3.2" stroke-linecap="round" /></svg>
                </div>
                <h2 class="mt-4 font-display text-lg font-semibold text-white">No movies found</h2>
                <p class="mt-1 max-w-sm text-sm text-slate-500">Try a different search term, genre, or filter.</p>
                <a href="{{ route('movies.index') }}" class="btn btn-ghost btn-sm mt-6">Reset filters</a>
            </div>
        @else
            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-5 lg:grid-cols-5">
                @foreach ($movies as $movie)
                    <x-movie-card :movie="$movie" />
                @endforeach
            </div>

            {{ $movies->links() }}
        @endif
    </div>
</x-layouts.public>
