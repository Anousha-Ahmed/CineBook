@php
    use App\Services\PricingService;
@endphp

<x-layouts.public :title="'Ticket ' . $ticket->seat->label . ' — CineBook'">
    <div class="mx-auto max-w-md px-4 py-10 sm:px-6">
        <div class="text-center">
            <p class="eyebrow">Entry pass</p>
            <h1 class="mt-2 font-display text-2xl font-semibold tracking-tight text-white">{{ $ticket->booking->showtime->movie->title }}</h1>
            <p class="mt-1 text-sm text-slate-400">{{ $ticket->booking->showtime->start_time->format('D j M Y, g:i A') }}</p>
        </div>

        <div class="card mt-7 overflow-hidden">
            <div class="relative p-6">
                <div class="mx-auto w-fit rounded-xl bg-white p-3">
                    <div class="qr-code" data-url="{{ $ticket->token }}" data-size="200"></div>
                </div>

                <p class="mt-4 break-all text-center font-mono text-[10px] leading-relaxed text-slate-500">{{ $ticket->token }}</p>

                <div class="mt-6 grid grid-cols-2 gap-4 border-t border-dashed border-white/10 pt-5 text-center">
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-slate-500">Seat</p>
                        <p class="mt-1 font-display text-xl font-semibold text-white">{{ $ticket->seat->label }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-slate-500">Screen</p>
                        <p class="mt-1 font-display text-xl font-semibold text-white">{{ $ticket->booking->showtime->screen->name }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-slate-500">Type</p>
                        <p class="mt-1 text-sm font-semibold text-white">{{ $ticket->seat->is_premium ? 'Premium' : 'Standard' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-slate-500">Status</p>
                        <p class="mt-1 text-sm font-semibold {{ $ticket->status === 'valid' ? 'text-emerald-400' : 'text-slate-400' }}">
                            {{ $ticket->status === 'used' ? 'Scanned' : ucfirst($ticket->status) }}
                        </p>
                    </div>
                </div>

                <div class="mt-5 border-t border-white/[.07] pt-4 text-center text-[11px] text-slate-500">
                    Booking #{{ str_pad($ticket->booking_id, 5, '0', STR_PAD_LEFT) }} &middot;
                    {{ PricingService::format($ticket->booking->total_amount_cents) }} &middot;
                    {{ $ticket->booking->showtime->screen->name }}
                </div>
            </div>

            <div class="border-t border-white/[.06] bg-white/[.02] px-6 py-4 text-center text-[11px] leading-relaxed text-slate-500 no-print">
                Present this code at the door. Scanning opens
                {{ \App\Support\BookingRules::SCAN_OPEN_MINUTES }} minutes before the show and closes
                {{ \App\Support\BookingRules::SCAN_CLOSE_MINUTES }} minutes after the start time.
            </div>
        </div>

        <div class="mt-6 flex flex-wrap justify-center gap-3 no-print">
            <button type="button" onclick="window.print()" class="btn btn-ghost btn-sm">Print / Save as PDF</button>
            <a href="{{ route('bookings.show', $ticket->booking_id) }}" class="btn btn-ghost btn-sm">Booking details</a>
            <a href="{{ route('bookings.index') }}" class="btn btn-ghost btn-sm">All bookings</a>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/qrcode.min.js') }}" defer></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const node = document.querySelector('.qr-code');
                if (!node || !window.QRCode) return;

                new QRCode(node, {
                    text: node.dataset.url,
                    width: Number(node.dataset.size),
                    height: Number(node.dataset.size),
                    colorDark: '#0b1120',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M,
                });
            });
        </script>
    @endpush
</x-layouts.public>
