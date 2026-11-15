<x-layouts.admin heading="{{ $showtime->exists ? 'Edit showtime' : 'New showtime' }}" subheading="{{ $showtime->exists ? $showtime->movie?->title.' · '.$showtime->start_time->format('D j M, g:i A') : 'Schedule a screening' }}">
    <x-slot name="actions">
        <a href="{{ route('admin.showtimes.index') }}" class="btn btn-ghost text-xs">Back to showtimes</a>
    </x-slot>

    <form method="POST" action="{{ $showtime->exists ? route('admin.showtimes.update', $showtime) : route('admin.showtimes.store') }}" class="max-w-2xl space-y-6">
        @csrf
        @if ($showtime->exists)
            @method('patch')
        @endif

        <div class="card space-y-5 p-6">
            <div>
                <h2 class="font-display text-base font-semibold text-white">Screening</h2>
                <p class="mt-1 text-xs text-slate-500">Pick the film, screen, and start time.</p>
            </div>

            <div>
                <label for="movie_id" class="label">Movie</label>
                <select id="movie_id" name="movie_id" class="input mt-1.5 w-full" required>
                    <option value="">Select a movie</option>
                    @foreach ($movies as $movie)
                        <option value="{{ $movie->id }}" @selected(old('movie_id', $showtime->movie_id) == $movie->id)>{{ $movie->title }}</option>
                    @endforeach
                </select>
                @error('movie_id') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="screen_id" class="label">Screen</label>
                    <select id="screen_id" name="screen_id" class="input mt-1.5 w-full" required>
                        <option value="">Select a screen</option>
                        @foreach ($screens as $screen)
                            <option value="{{ $screen->id }}" @selected(old('screen_id', $showtime->screen_id) == $screen->id)>{{ $screen->name }}</option>
                        @endforeach
                    </select>
                    @error('screen_id') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="status" class="label">Status</label>
                    <select id="status" name="status" class="input mt-1.5 w-full" required>
                        <option value="scheduled" @selected(old('status', $showtime->status) === 'scheduled')>Scheduled</option>
                        <option value="cancelled" @selected(old('status', $showtime->status) === 'cancelled')>Cancelled</option>
                    </select>
                    @error('status') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="start_time" class="label">Start time</label>
                    <input id="start_time" name="start_time" type="datetime-local"
                           value="{{ old('start_time', $showtime->exists ? $showtime->start_time->format('Y-m-d\TH:i') : now()->addDay()->format('Y-m-d\TH:i')) }}"
                           class="input mt-1.5 w-full" required>
                    @error('start_time') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="base_price_cents" class="label">Base price (cents)</label>
                    <input id="base_price_cents" name="base_price_cents" type="number" min="0" max="100000" step="50"
                           value="{{ old('base_price_cents', $showtime->base_price_cents ?? 1000) }}" class="input mt-1.5 w-full" required>
                    <p class="mt-1.5 text-xs text-slate-500">1000 = $10.00. Premium seats add the surcharge from settings.</p>
                    @error('base_price_cents') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn btn-primary">{{ $showtime->exists ? 'Save changes' : 'Schedule showtime' }}</button>
            <a href="{{ route('admin.showtimes.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</x-layouts.admin>
