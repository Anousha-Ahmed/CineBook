<x-layouts.admin heading="Audit log" subheading="{{ $logs->total() }} recorded actions">
    <div class="space-y-5">
        <form method="GET" action="{{ route('admin.audit.index') }}" class="grid gap-3 rounded-2xl border border-white/[.07] bg-night-900/60 p-4 sm:grid-cols-[1fr_auto]">
            <input type="search" name="search" value="{{ request('search') }}" class="input" placeholder="Action, target or admin email">
            <button type="submit" class="btn btn-ghost">Search</button>
        </form>

        <div class="table-shell">
            @if ($logs->isEmpty())
                <div class="px-6 py-16 text-center">
                    <p class="font-display text-lg font-semibold text-white">No audit entries</p>
                    <p class="mt-1 text-sm text-slate-500">Admin and staff actions will appear here.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-white/[.02] text-[11px] uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3.5 font-medium">When</th>
                                <th class="px-5 py-3.5 font-medium">Actor</th>
                                <th class="px-5 py-3.5 font-medium">Action</th>
                                <th class="px-5 py-3.5 font-medium">Target</th>
                                <th class="px-5 py-3.5 font-medium">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[.05]">
                            @foreach ($logs as $log)
                                <tr class="align-top">
                                    <td class="px-5 py-3.5 text-xs text-slate-500">{{ $log->created_at->format('j M Y, g:i A') }}</td>
                                    <td class="px-5 py-3.5">
                                        <p class="text-slate-300">{{ $log->actor?->name ?? 'System' }}</p>
                                        <p class="text-xs text-slate-600">{{ $log->actor?->email ?? $log->ip }}</p>
                                    </td>
                                    <td class="px-5 py-3.5"><span class="badge badge-slate">{{ $log->action }}</span></td>
                                    <td class="px-5 py-3.5 text-slate-400">
                                        {{ class_basename($log->target_type ?? '—') }}{{ $log->target_id ? ' #'.$log->target_id : '' }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if ($log->before || $log->after || $log->meta)
                                            <details class="text-xs text-slate-500">
                                                <summary class="cursor-pointer text-brand-400">View</summary>
                                                <pre class="mt-2 max-w-xs overflow-x-auto rounded-lg bg-black/30 p-2 text-[10px] leading-relaxed">{{ json_encode(['before' => $log->before, 'after' => $log->after, 'meta' => $log->meta], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </details>
                                        @else
                                            <span class="text-xs text-slate-600">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-white/[.06] px-5 py-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
