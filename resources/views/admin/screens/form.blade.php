<x-layouts.admin heading="{{ $screen->exists ? 'Manage screen' : 'New screen' }}" subheading="{{ $screen->exists ? $screen->name : 'Generate a seat grid for a new auditorium' }}">
    <x-slot name="actions">
        <a href="{{ route('admin.screens.index') }}" class="btn btn-ghost text-xs">Back to screens</a>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[22rem_minmax(0,1fr)]">
        <form method="POST" action="{{ $screen->exists ? route('admin.screens.update', $screen) : route('admin.screens.store') }}" class="card h-fit space-y-5 p-6">
            @csrf
            @if ($screen->exists)
                @method('patch')
            @endif

            <div>
                <label for="name" class="label">Screen name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $screen->name) }}" class="input mt-1.5 w-full" required autofocus>
                @error('name') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="rows" class="label">Rows</label>
                    <input id="rows" name="rows" type="number" min="1" max="26" value="{{ old('rows', $screen->rows ?? 8) }}" class="input mt-1.5 w-full" required>
                    @error('rows') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="seats_per_row" class="label">Seats per row</label>
                    <input id="seats_per_row" name="seats_per_row" type="number" min="1" max="40" value="{{ old('seats_per_row', $screen->seats_per_row ?? 12) }}" class="input mt-1.5 w-full" required>
                    @error('seats_per_row') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="premium_rows" class="label">Premium rows</label>
                <input id="premium_rows" name="premium_rows" type="number" min="0" max="26" value="{{ old('premium_rows', $screen->premium_rows ?? 2) }}" class="input mt-1.5 w-full" required>
                <p class="mt-1.5 text-[11px] leading-relaxed text-slate-500">Row count from the back of the auditorium. Premium seats use the premium surcharge setting.</p>
                @error('premium_rows') <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            @if ($screen->exists && $screen->showtimes()->exists())
                <p class="rounded-lg border border-amber-500/20 bg-amber-500/10 px-3 py-2 text-[11px] leading-relaxed text-amber-200">
                    This screen already has showtimes. The seat layout cannot be regenerated, but you can rename it and enable or disable individual seats.
                </p>
            @endif

            <button type="submit" class="btn btn-primary w-full">{{ $screen->exists ? 'Save screen' : 'Create screen' }}</button>
        </form>

        @if ($screen->exists)
            <div class="card p-5 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="font-display text-base font-semibold text-white">Seat map</h2>
                        <p class="mt-1 text-xs text-slate-500">{{ $screen->seats->count() }} seats. Click a seat to enable or disable it.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-500">
                        <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-sm border border-white/15 bg-night-700"></span> Standard</span>
                        <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-sm border border-amber-400/30 bg-amber-500/15"></span> Premium</span>
                        <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-sm border border-red-500/30 bg-red-500/15"></span> Disabled</span>
                    </div>
                </div>

                <div class="mt-6 overflow-x-auto pb-2">
                    <div class="mx-auto flex w-max flex-col items-center gap-1.5">
                        @foreach ($screen->seats->groupBy('row') as $row => $rowSeats)
                            <div class="flex items-center gap-1.5">
                                <span class="w-5 text-center text-[11px] font-semibold text-slate-500">{{ $row }}</span>
                                @foreach ($rowSeats as $seat)
                                    <form method="POST" action="{{ route('admin.screens.seats.toggle', [$screen, $seat]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" title="{{ $seat->label }}{{ $seat->is_disabled ? ' — disabled' : '' }}"
                                                class="flex h-8 w-8 items-center justify-center rounded-md border text-[10px] font-semibold transition
                                                    {{ $seat->is_disabled
                                                        ? 'border-red-500/30 bg-red-500/15 text-red-300'
                                                        : ($seat->is_premium ? 'border-amber-400/30 bg-amber-500/15 text-amber-200 hover:bg-amber-500/25' : 'border-white/15 bg-night-700 text-slate-300 hover:bg-night-600') }}">
                                            {{ $seat->number }}
                                        </button>
                                    </form>
                                @endforeach
                                <span class="w-5 text-center text-[11px] font-semibold text-slate-500">{{ $row }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="card flex h-fit flex-col items-center justify-center p-10 text-center">
                <p class="font-display text-lg font-semibold text-white">Seat grid is generated on save</p>
                <p class="mt-2 max-w-sm text-sm text-slate-500">
                    Choose how many rows and seats per row. The last rows are marked premium, then you can disable broken seats from the seat map.
                </p>
            </div>
        @endif
    </div>
</x-layouts.admin>
