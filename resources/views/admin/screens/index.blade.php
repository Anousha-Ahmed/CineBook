<x-layouts.admin heading="Screens" subheading="Auditoriums, seat layouts and premium rows">
    <x-slot name="actions">
        <a href="{{ route('admin.screens.create') }}" class="btn btn-primary text-xs">New screen</a>
    </x-slot>

    <div class="table-shell">
        @if ($screens->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="font-display text-lg font-semibold text-white">No screens yet</p>
                <p class="mt-1 text-sm text-slate-500">Create a screen to start scheduling showtimes.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-white/[.02] text-[11px] uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5 font-medium">Screen</th>
                            <th class="px-5 py-3.5 font-medium">Layout</th>
                            <th class="px-5 py-3.5 font-medium">Seats</th>
                            <th class="px-5 py-3.5 font-medium">Showtimes</th>
                            <th class="px-5 py-3.5 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[.05]">
                        @foreach ($screens as $screen)
                            <tr class="transition hover:bg-white/[.02]">
                                <td class="px-5 py-3.5 font-medium text-slate-200">{{ $screen->name }}</td>
                                <td class="px-5 py-3.5 text-slate-400">
                                    @if ($screen->rows)
                                        {{ $screen->rows }} rows &times; {{ $screen->seats_per_row }} seats
                                    @else
                                        <span class="text-xs text-slate-600">Not generated</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-slate-400">
                                    {{ $screen->seats_count }} total
                                    <span class="text-xs text-slate-600">&middot; {{ $screen->premium_count }} premium</span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-400">{{ $screen->showtimes_count }}</td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.screens.edit', $screen) }}" class="rounded-lg border border-white/10 px-2.5 py-1.5 text-xs font-medium text-slate-300 transition hover:bg-white/[.04]">Manage</a>
                                        <form method="POST" action="{{ route('admin.screens.destroy', $screen) }}" onsubmit="return confirm('Delete this screen and its seats?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-500/25 px-2.5 py-1.5 text-xs font-medium text-red-300 transition hover:bg-red-500/10">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layouts.admin>
