<x-layouts.admin heading="Sales report" subheading="{{ $from->format('j M Y') }} &rarr; {{ $to->format('j M Y') }}">
    <x-slot name="actions">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.reports.export', ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}" class="btn btn-primary text-xs">Export sales CSV</a>
            <a href="{{ route('admin.reports.bookings', ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}" class="btn btn-ghost text-xs">Export bookings CSV</a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid gap-3 rounded-2xl border border-white/[.07] bg-night-900/60 p-4 sm:grid-cols-[1fr_1fr_auto]">
            <div>
                <label for="from" class="label">From</label>
                <input id="from" name="from" type="date" value="{{ $from->toDateString() }}" class="input mt-1.5 w-full">
            </div>
            <div>
                <label for="to" class="label">To</label>
                <input id="to" name="to" type="date" value="{{ $to->toDateString() }}" class="input mt-1.5 w-full">
            </div>
            <button type="submit" class="btn btn-ghost self-end">Apply</button>
        </form>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @php
                $cards = [
                    ['label' => 'Gross revenue', 'value' => \App\Services\PricingService::format($summary['gross_cents'])],
                    ['label' => 'Refunded', 'value' => \App\Services\PricingService::format($summary['refunded_cents'])],
                    ['label' => 'Net revenue', 'value' => \App\Services\PricingService::format($summary['net_cents'])],
                    ['label' => 'Tickets', 'value' => number_format($summary['tickets'])],
                ];
            @endphp

            @foreach ($cards as $card)
                <div class="card p-5">
                    <p class="text-xs font-medium uppercase tracking-[.16em] text-slate-500">{{ $card['label'] }}</p>
                    <p class="mt-3 font-display text-2xl font-semibold text-white">{{ $card['value'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="table-shell">
                <div class="border-b border-white/[.06] px-5 py-4">
                    <h2 class="font-display text-base font-semibold text-white">Sales by day</h2>
                    <p class="text-xs text-slate-500">{{ $summary['bookings'] }} bookings &middot; {{ $summary['confirmed'] }} confirmed &middot; {{ $summary['cancelled'] }} cancelled or refunded</p>
                </div>

                @if ($daily->isEmpty())
                    <p class="px-5 py-10 text-center text-sm text-slate-500">No sales in this range.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-white/[.02] text-[11px] uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-5 py-3 font-medium">Date</th>
                                    <th class="px-5 py-3 font-medium">Tickets</th>
                                    <th class="px-5 py-3 font-medium">Bookings</th>
                                    <th class="px-5 py-3 text-right font-medium">Gross</th>
                                    <th class="px-5 py-3 text-right font-medium">Refunds</th>
                                    <th class="px-5 py-3 text-right font-medium">Net</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[.05]">
                                @foreach ($daily as $row)
                                    <tr>
                                        <td class="px-5 py-3 text-slate-200">{{ \Illuminate\Support\Carbon::parse($row->day)->format('D j M Y') }}</td>
                                        <td class="px-5 py-3 text-slate-400">{{ $row->tickets }}</td>
                                        <td class="px-5 py-3 text-slate-400">{{ $row->bookings }}</td>
                                        <td class="px-5 py-3 text-right text-slate-200">{{ \App\Services\PricingService::format($row->gross_cents) }}</td>
                                        <td class="px-5 py-3 text-right text-red-300">{{ $row->refund_cents > 0 ? \App\Services\PricingService::format($row->refund_cents) : '—' }}</td>
                                        <td class="px-5 py-3 text-right font-medium text-slate-200">{{ \App\Services\PricingService::format($row->net_cents) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="table-shell">
                <div class="border-b border-white/[.06] px-5 py-4">
                    <h2 class="font-display text-base font-semibold text-white">Sales by movie</h2>
                    <p class="text-xs text-slate-500">Successful payments grouped by title</p>
                </div>

                @if ($perMovie->isEmpty())
                    <p class="px-5 py-10 text-center text-sm text-slate-500">No sales in this range.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-white/[.02] text-[11px] uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-5 py-3 font-medium">Movie</th>
                                    <th class="px-5 py-3 font-medium">Tickets</th>
                                    <th class="px-5 py-3 font-medium">Bookings</th>
                                    <th class="px-5 py-3 text-right font-medium">Gross</th>
                                    <th class="px-5 py-3 text-right font-medium">Refunds</th>
                                    <th class="px-5 py-3 text-right font-medium">Net</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[.05]">
                                @foreach ($perMovie as $row)
                                    <tr>
                                        <td class="px-5 py-3 text-slate-200">{{ $row->title }}</td>
                                        <td class="px-5 py-3 text-slate-400">{{ $row->tickets }}</td>
                                        <td class="px-5 py-3 text-slate-400">{{ $row->bookings }}</td>
                                        <td class="px-5 py-3 text-right text-slate-200">{{ \App\Services\PricingService::format($row->gross_cents) }}</td>
                                        <td class="px-5 py-3 text-right text-red-300">{{ $row->refund_cents > 0 ? \App\Services\PricingService::format($row->refund_cents) : '—' }}</td>
                                        <td class="px-5 py-3 text-right font-medium text-slate-200">{{ \App\Services\PricingService::format($row->net_cents) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin>
