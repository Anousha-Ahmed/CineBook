<x-layouts.admin heading="{{ $movie->exists ? 'Edit movie' : 'Add movie' }}" subheading="{{ $movie->exists ? $movie->title : 'Create a new title for the catalogue' }}">
    <x-slot name="actions">
        <a href="{{ route('admin.movies.index') }}" class="btn btn-ghost text-xs">Back to movies</a>
    </x-slot>

    <form method="POST" action="{{ $movie->exists ? route('admin.movies.update', $movie) : route('admin.movies.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
        @csrf
        @if ($movie->exists)
            @method('patch')
        @endif

        <div class="card space-y-5 p-6">
            <div>
                <h2 class="font-display text-base font-semibold text-white">Details</h2>
                <p class="mt-1 text-xs text-slate-500">Shown on the movie page and cards.</p>
            </div>

            <div>
                <label for="title" class="label">Title</label>
                <input id="title" name="title" type="text" value="{{ old('title', $movie->title) }}" class="input mt-1.5 w-full" required autofocus>
                @error('title') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="synopsis" class="label">Synopsis</label>
                <textarea id="synopsis" name="synopsis" rows="5" class="input mt-1.5 w-full">{{ old('synopsis', $movie->synopsis) }}</textarea>
                @error('synopsis') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="duration_minutes" class="label">Duration (minutes)</label>
                    <input id="duration_minutes" name="duration_minutes" type="number" min="20" max="400" value="{{ old('duration_minutes', $movie->duration_minutes) }}" class="input mt-1.5 w-full" required>
                    @error('duration_minutes') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="rating" class="label">Age rating</label>
                    <input id="rating" name="rating" type="text" value="{{ old('rating', $movie->rating) }}" class="input mt-1.5 w-full" placeholder="PG-13" required>
                    @error('rating') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-[minmax(0,1fr)_10rem]">
                <div>
                    <label for="poster" class="label">Poster upload</label>
                    <input id="poster" name="poster" type="file" accept="image/jpeg,image/png,image/webp" class="input mt-1.5 w-full file:mr-3 file:rounded-md file:border-0 file:bg-brand-600/20 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-brand-200">
                    <p class="mt-1.5 text-[11px] text-slate-500">JPG, PNG or WebP up to 4 MB. Uploading replaces the current poster.</p>
                    @error('poster') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    @if ($movie->poster_url)
                        <img src="{{ $movie->poster_url }}" alt="" class="h-36 w-24 rounded-lg border border-white/10 object-cover">
                    @else
                        <div class="flex h-36 w-24 items-center justify-center rounded-lg border border-dashed border-white/10 text-[10px] uppercase tracking-widest text-slate-600">No poster</div>
                    @endif
                </div>
            </div>

            <div>
                <label for="poster_url" class="label">Poster URL</label>
                <input id="poster_url" name="poster_url" type="url" value="{{ old('poster_url', $movie->poster_url) }}" class="input mt-1.5 w-full" placeholder="https://example.com/poster.jpg">
                @error('poster_url') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="trailer_url" class="label">Trailer URL</label>
                <input id="trailer_url" name="trailer_url" type="url" value="{{ old('trailer_url', $movie->trailer_url) }}" class="input mt-1.5 w-full" placeholder="https://www.youtube.com/watch?v=...">
                <p class="mt-1.5 text-[11px] text-slate-500">YouTube links are embedded on the movie page. Other links open in a new tab.</p>
                @error('trailer_url') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-white/[.07] bg-white/[.02] px-4 py-3">
                <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $movie->is_featured)) class="mt-0.5 h-4 w-4 rounded border-white/20 bg-night-800 text-brand-500 focus:ring-brand-500/40">
                <span>
                    <span class="block text-sm font-medium text-slate-200">Feature on the home page</span>
                    <span class="block text-xs text-slate-500">Featured titles are shown in the hero carousel.</span>
                </span>
            </label>
        </div>

        <div class="card p-6">
            <h2 class="font-display text-base font-semibold text-white">Genres</h2>
            <p class="mt-1 text-xs text-slate-500">Pick one or more genres.</p>

            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($genres as $genre)
                    @php
                        $checked = in_array($genre->id, old('genres', $movie->genres->pluck('id')->all()));
                    @endphp
                    <label class="cursor-pointer">
                        <input type="checkbox" name="genres[]" value="{{ $genre->id }}" class="peer sr-only" @checked($checked)>
                        <span class="inline-flex rounded-full border border-white/10 px-3.5 py-1.5 text-xs font-medium text-slate-400 transition peer-checked:border-brand-500/40 peer-checked:bg-brand-600/15 peer-checked:text-white hover:border-white/25">
                            {{ $genre->name }}
                        </span>
                    </label>
                @endforeach
            </div>
            @error('genres') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn btn-primary">{{ $movie->exists ? 'Save changes' : 'Create movie' }}</button>
            <a href="{{ route('admin.movies.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</x-layouts.admin>
