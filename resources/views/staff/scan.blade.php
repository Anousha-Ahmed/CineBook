<x-app-layout :title="__('Scan tickets — CineBook')">
    <x-slot name="header">
        <p class="eyebrow">{{ __('Front of house') }}</p>
        <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-white">{{ __('Ticket scanner') }}</h1>
        <p class="mt-2 text-sm text-slate-400">{{ __('Scan or paste a ticket code to check it in. Scanning opens 60 minutes before showtime and closes 30 minutes after.') }}</p>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
        <div class="space-y-6">
            @if ($showtimes->isNotEmpty())
                <form method="GET" action="{{ route('staff.scan') }}" class="card flex flex-col gap-4 p-4 sm:flex-row sm:items-end sm:justify-between">
                    <div class="min-w-0 flex-1">
                        <label for="showtime" class="label">{{ __('Showtime') }}</label>
                        <select id="showtime" name="showtime" onchange="this.form.submit()" class="input mt-1.5 w-full">
                            @foreach ($showtimes as $showtime)
                                <option value="{{ $showtime->id }}" @selected($selected?->id === $showtime->id)>
                                    {{ $showtime->start_time->format('g:i A') }} — {{ $showtime->movie->title }} ({{ $showtime->screen->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-right">
                        <p class="text-[11px] uppercase tracking-[.18em] text-slate-500">{{ __('Scanned') }}</p>
                        <p class="font-display text-2xl font-semibold text-white">{{ $scanned }}<span class="text-base text-slate-500"> / {{ $total }}</span></p>
                    </div>
                </form>
            @else
                <div class="card px-5 py-4">
                    <p class="text-sm text-slate-400">{{ __('No showtimes are scheduled for today. Tickets from any showtime can still be scanned below.') }}</p>
                </div>
            @endif

            <div class="card p-6 sm:p-8">
                <form method="POST" action="{{ route('staff.scan.verify') }}" x-data="ticketScanner()" x-ref="scanForm">
                    @csrf
                    @if ($selected)
                        <input type="hidden" name="showtime_id" value="{{ $selected->id }}">
                    @endif

                    <label for="token" class="label">{{ __('Ticket code') }}</label>
                    <div class="mt-2 flex flex-col gap-3 sm:flex-row">
                        <input id="token" name="token" type="text" x-ref="tokenInput" value="{{ old('token') }}"
                               class="input w-full font-mono text-lg tracking-wide" placeholder="Paste the 40-character code" required autocomplete="off" autofocus>
                        <button type="submit" class="btn btn-primary shrink-0">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m4 12 5 5L20 6" /></svg>
                            {{ __('Validate') }}
                        </button>
                    </div>
                    @error('token')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror

                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <button type="button" @click="toggleCamera()" class="btn btn-ghost" x-bind:disabled="!supported">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 8V6a2 2 0 0 1 2-2h2M16 4h2a2 2 0 0 1 2 2v2M20 16v2a2 2 0 0 1-2 2h-2M8 20H6a2 2 0 0 1-2-2v-2" />
                                <circle cx="12" cy="12" r="3.2" />
                            </svg>
                            <span x-text="scanning ? 'Stop camera' : 'Scan with camera'"></span>
                        </button>
                        <p class="text-xs text-slate-500" x-show="!supported" x-cloak>{{ __('Camera scanning is not supported in this browser — type the code instead.') }}</p>
                        <p class="text-xs text-slate-500" x-show="scanning" x-cloak x-text="status"></p>
                    </div>

                    <div class="mt-4 overflow-hidden rounded-xl border border-white/10 bg-black" x-show="scanning" x-cloak>
                        <video x-ref="video" class="aspect-video w-full object-cover" playsinline muted></video>
                    </div>
                </form>
            </div>

            @if (session('scan'))
                @php
                    $scan = session('scan');
                    $admitted = $scan['result'] === 'valid';
                    $headline = $admitted ? 'Admitted' : $scan['message'];
                @endphp
                <div class="overflow-hidden rounded-2xl ring-1 ring-inset {{ $admitted ? 'bg-emerald-500/15 ring-emerald-500/40' : 'bg-red-500/15 ring-red-500/40' }}">
                    <div class="flex items-center gap-5 px-6 py-7">
                        <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl {{ $admitted ? 'bg-emerald-500/20 text-emerald-300' : 'bg-red-500/20 text-red-300' }}">
                            @if ($admitted)
                                <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m4 12.5 5 5L20 6.5" /></svg>
                            @else
                                <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v5M12 16.5v.5" /><circle cx="12" cy="12" r="9" /></svg>
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="font-display text-3xl font-bold uppercase tracking-wide {{ $admitted ? 'text-emerald-300' : 'text-red-300' }} sm:text-4xl">
                                {{ $headline }}
                            </p>

                            @if ($scan['summary'])
                                <p class="mt-2 text-base font-medium text-white">
                                    {{ $scan['summary']['movie'] }} &middot; Seat {{ $scan['summary']['seat'] }} &middot; {{ $scan['summary']['screen'] }}
                                </p>
                                <p class="mt-1 text-sm text-slate-300">{{ $scan['summary']['start'] }} &middot; Booking #{{ $scan['summary']['booking'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="font-display text-base font-semibold text-white">{{ __('Scan rules') }}</h2>
                <ul class="mt-4 space-y-3 text-sm text-slate-400">
                    <li class="flex gap-3"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500"></span>{{ __('A ticket is valid once — a second scan reports it as already used.') }}</li>
                    <li class="flex gap-3"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500"></span>{{ __('Scanning opens 60 minutes before the showtime and closes 30 minutes after it starts.') }}</li>
                    <li class="flex gap-3"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500"></span>{{ __('A ticket for a different showtime is rejected — check the showtime selector above.') }}</li>
                    <li class="flex gap-3"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500"></span>{{ __('Cancelled and refunded bookings, and void tickets, are rejected.') }}</li>
                    <li class="flex gap-3"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500"></span>{{ __('Every attempt is logged for the admin audit trail.') }}</li>
                </ul>
            </div>

            <div class="card overflow-hidden">
                <div class="border-b border-white/[.06] px-6 py-4">
                    <h2 class="font-display text-base font-semibold text-white">{{ __('Recent scans') }}</h2>
                </div>

                @if ($recent->isEmpty())
                    <p class="px-6 py-8 text-center text-sm text-slate-500">{{ __('No tickets scanned yet.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-white/[.02] text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-6 py-3 font-medium">{{ __('Ticket') }}</th>
                                    <th class="px-6 py-3 font-medium">{{ __('Result') }}</th>
                                    <th class="px-6 py-3 font-medium">{{ __('By') }}</th>
                                    <th class="px-6 py-3 font-medium">{{ __('When') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[.05]">
                                @foreach ($recent as $scan)
                                    <tr>
                                        <td class="px-6 py-3">
                                            <p class="font-medium text-slate-200">{{ $scan->ticket?->booking?->showtime?->movie?->title ?? '—' }}</p>
                                            <p class="text-xs text-slate-500">{{ $scan->ticket?->seat?->label }} &middot; #{{ $scan->ticket?->booking_id }}</p>
                                        </td>
                                        <td class="px-6 py-3">
                                            <span class="{{ $scan->result === 'valid' ? 'badge badge-green' : 'badge badge-red' }}">{{ ucfirst(str_replace('_', ' ', $scan->result)) }}</span>
                                        </td>
                                        <td class="px-6 py-3 text-slate-400">{{ $scan->staff?->name ?? '—' }}</td>
                                        <td class="px-6 py-3 text-xs text-slate-500">{{ $scan->created_at->format('j M, g:i A') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('load', () => {
            const input = document.getElementById('token');
            if (input && !input.value) input.focus();
        });

        function ticketScanner() {
            return {
                scanning: false,
                supported: typeof BarcodeDetector !== 'undefined' && !!navigator.mediaDevices?.getUserMedia,
                status: '',
                stream: null,
                detector: null,
                frame: null,

                async toggleCamera() {
                    if (this.scanning) {
                        return this.stopCamera();
                    }

                    try {
                        this.detector = new BarcodeDetector({ formats: ['qr_code'] });
                        this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                        this.$refs.video.srcObject = this.stream;
                        await this.$refs.video.play();
                        this.scanning = true;
                        this.status = 'Point the camera at the ticket QR code.';
                        this.tick();
                    } catch (error) {
                        this.status = 'Camera unavailable: ' + error.message;
                    }
                },

                stopCamera() {
                    this.scanning = false;
                    this.status = '';
                    cancelAnimationFrame(this.frame);
                    this.stream?.getTracks().forEach(track => track.stop());
                    this.stream = null;
                },

                async tick() {
                    if (!this.scanning) return;

                    try {
                        const codes = await this.detector.detect(this.$refs.video);
                        if (codes.length) {
                            const value = codes[0].rawValue || '';
                            const token = value.split('/').filter(Boolean).pop() || value;
                            this.$refs.tokenInput.value = token;
                            this.stopCamera();
                            this.$refs.scanForm.submit();
                            return;
                        }
                    } catch (error) {
                        this.status = 'Scanner error: ' + error.message;
                    }

                    this.frame = requestAnimationFrame(() => this.tick());
                },
            };
        }
    </script>
</x-app-layout>
