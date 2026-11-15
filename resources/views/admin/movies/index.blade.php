<x-layouts.admin heading="Movies" subheading="{{ $movies->total() }} titles in the catalogue">
    <x-slot name="actions">
        <a href="{{ route('admin.movies.create') }}" class="btn btn-primary text-xs">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14" /></svg>
            Add movie
        </a>
    </x-slot>

    <div class="space-y-5">
        <form method="GET" action="{{ route('admin.movies.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" /></svg>
                <input type="search" name="search" value="{{ request('search') }}" class="input w-full pl-10" placeholder="Search by title or synopsis">
            </div>
            <button type="submit" class="btn btn-ghost">Search</button>
        </form>

        <div class="table-shell">
            @if ($movies->isEmpty())
                <div class="px-6 py-16 text-center">
                    <p class="font-display text-lg font-semibold text-white">No movies found</p>
                    <p class="mt-1 text-sm text-slate-500">Try a different search or add a new title.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-white/[.02] text-[11px] uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3.5 font-medium">Movie</th>
                                <th class="px-5 py-3.5 font-medium">Genres</th>
                                <th class="px-5 py-3.5 font-medium">Duration</th>
                                <th class="px-5 py-3.5 font-medium">Showtimes</th>
                                <th class="px-5 py-3.5 font-medium">Status</th>
                                <th class="px-5 py-3.5 text-right font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[.05]">
                            @foreach ($movies as $movie)
                                <tr class="transition hover:bg-white/[.02]">
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="h-14 w-10 shrink-0 overflow-hidden rounded-md bg-night-800 ring-1 ring-white/10">
                                                @if ($movie->poster_url)
                                                    <img src="{{ $movie->poster_url }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate font-medium text-slate-200">{{ $movie->title }}</p>
                                                <p class="text-xs text-slate-500">{{ $movie->rating }} &middot; {{ $movie->duration_minutes }} min</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse ($movie->genres as $genre)
                                                <span class="rounded-md bg-white/[.05] px-2 py-0.5 text-[11px] text-slate-400">{{ $genre->name }}</span>
                                            @empty
                                                <span class="text-xs text-slate-600">—</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-400">{{ $movie->duration_minutes }} min</td>
                                    <td class="px-5 py-3.5 text-slate-400">{{ $movie->showtimes_count }}</td>
                                    <td class="px-5 py-3.5">
                                        @if ($movie->is_featured)
                                            <span class="badge badge-blue">Featured</span>
                                        @else
                                            <span class="badge badge-slate">Standard</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.movies.edit', $movie) }}" class="rounded-lg border border-white/10 px-2.5 py-1.5 text-xs font-medium text-slate-300 transition hover:border-white/25 hover:text-white">Edit</a>
                                            <form method="POST" action="{{ route('admin.movies.destroy', $movie) }}" onsubmit="return confirm('Delete this movie? This cannot be undone.')">
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
                    {{ $movies->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
