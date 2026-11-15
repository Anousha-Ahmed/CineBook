<x-layouts.admin heading="Failed jobs" subheading="Queued emails and refunds that exhausted their retries">
    <div class="space-y-5">
        @if ($jobs->isEmpty())
            <div class="card px-6 py-16 text-center">
                <p class="font-display text-lg font-semibold text-white">Queue is healthy</p>
                <p class="mt-1 text-sm text-slate-500">No failed jobs are waiting for attention.</p>
            </div>
        @else
            <div class="table-shell">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-white/[.02] text-[11px] uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3.5 font-medium">Job</th>
                                <th class="px-5 py-3.5 font-medium">Queue</th>
                                <th class="px-5 py-3.5 font-medium">Failed at</th>
                                <th class="px-5 py-3.5 font-medium">Error</th>
                                <th class="px-5 py-3.5 text-right font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[.05]">
                            @foreach ($jobs as $job)
                                @php
                                    $payload = json_decode($job->payload, true);
                                    $name = class_basename($payload['displayName'] ?? 'Unknown job');
                                    $message = str($job->exception)->after('): ')->before("\n")->limit(120);
                                @endphp
                                <tr class="align-top transition hover:bg-white/[.02]">
                                    <td class="px-5 py-3.5">
                                        <p class="font-medium text-slate-200">{{ $name }}</p>
                                        <p class="font-mono text-[10px] text-slate-600">{{ $job->uuid }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-400">{{ $job->queue }}</td>
                                    <td class="px-5 py-3.5 text-slate-400">{{ \Illuminate\Support\Carbon::parse($job->failed_at)->format('j M Y, g:i A') }}</td>
                                    <td class="max-w-sm px-5 py-3.5 text-xs text-red-300/80">{{ $message }}</td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center justify-end gap-2">
                                            <form method="POST" action="{{ route('admin.failed-jobs.retry', $job->uuid) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-brand-500/25 px-2.5 py-1.5 text-xs font-medium text-brand-300 transition hover:bg-brand-500/10">Retry</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.failed-jobs.destroy', $job->uuid) }}" onsubmit="return confirm('Remove this failed job from the list?')">
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

                <div class="border-t border-white/[.06] px-5 py-4">
                    {{ $jobs->links() }}
                </div>
            </div>
        @endif
    </div>
</x-layouts.admin>
