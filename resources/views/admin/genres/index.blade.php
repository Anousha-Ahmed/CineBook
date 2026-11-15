<x-layouts.admin heading="Genres" subheading="Categories used to filter the movie catalogue">
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="table-shell">
            @if ($genres->isEmpty())
                <div class="px-6 py-16 text-center">
                    <p class="font-display text-lg font-semibold text-white">No genres yet</p>
                    <p class="mt-1 text-sm text-slate-500">Add your first genre on the right.</p>
                </div>
            @else
                <table class="w-full text-left text-sm">
                    <thead class="bg-white/[.02] text-[11px] uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5 font-medium">Genre</th>
                            <th class="px-5 py-3.5 font-medium">Movies</th>
                            <th class="px-5 py-3.5 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[.05]">
                        @foreach ($genres as $genre)
                            <tr class="transition hover:bg-white/[.02]">
                                <td class="px-5 py-3">
                                    <form method="POST" action="{{ route('admin.genres.update', $genre) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input name="name" value="{{ $genre->name }}" class="input h-9 w-56" required>
                                        <button type="submit" class="text-xs font-medium text-brand-400 hover:text-brand-300">Save</button>
                                    </form>
                                </td>
                                <td class="px-5 py-3 text-slate-400">{{ $genre->movies_count }}</td>
                                <td class="px-5 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.genres.destroy', $genre) }}" onsubmit="return confirm('Delete this genre?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-500/25 px-2.5 py-1.5 text-xs font-medium text-red-300 transition hover:bg-red-500/10">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.genres.store') }}" class="card h-fit space-y-4 p-6">
            @csrf
            <div>
                <h2 class="font-display text-base font-semibold text-white">Add a genre</h2>
                <p class="mt-1 text-xs text-slate-500">Genres appear as filters on the movies page.</p>
            </div>
            <div>
                <label for="name" class="label">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" class="input mt-1.5 w-full" placeholder="Documentary" required>
                @error('name') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn btn-primary w-full">Add genre</button>
        </form>
    </div>
</x-layouts.admin>
