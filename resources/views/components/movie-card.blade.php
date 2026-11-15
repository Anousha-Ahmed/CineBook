@props(['movie'])

@php
    $poster = $movie->poster_url;
    $genre = $movie->genres->first()->name ?? 'Feature';
    $nextShow = $movie->next_showtime_at ?? null;
@endphp

<a href="{{ route('movies.show', $movie) }}" class="group block">
    <div class="relative aspect-[2/3] overflow-hidden rounded-2xl border border-white/[.08] bg-night-800 shadow-card">
        @if ($poster)
            <img src="{{ $poster }}" alt="{{ $movie->title }} poster"
                 class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]" loading="lazy">
        @else
            <div class="flex h-full items-center justify-center bg-gradient-to-br from-night-700 to-night-900 p-4 text-center font-display text-lg text-slate-500">
                {{ $movie->title }}
            </div>
        @endif

        <div class="absolute inset-0 bg-gradient-to-t from-night-950 via-night-950/10 to-transparent opacity-80"></div>

        <span class="absolute left-3 top-3 rounded-md bg-black/60 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur">
            {{ $movie->rating }}
        </span>

        @if ($nextShow)
            <span class="absolute inset-x-3 bottom-3 rounded-lg bg-brand-600/90 px-2.5 py-1.5 text-center text-[11px] font-semibold text-white opacity-0 backdrop-blur transition group-hover:opacity-100">
                Next show {{ \Illuminate\Support\Carbon::parse($nextShow)->format('D, g:i A') }}
            </span>
        @endif
    </div>

    <div class="mt-3">
        <h3 class="truncate font-display text-[15px] font-semibold text-white transition group-hover:text-brand-400">{{ $movie->title }}</h3>
        <p class="mt-1 text-xs text-slate-500">
            {{ $movie->duration_minutes }} min &middot; {{ $genre }}
        </p>
    </div>
</a>
