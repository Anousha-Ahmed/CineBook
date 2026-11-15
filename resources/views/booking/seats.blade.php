@php
    use App\Services\PricingService;
@endphp

<x-layouts.public :title="'Select seats — ' . $showtime->movie->title">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Breadcrumb / progress --}}
        <nav class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('movies.show', $showtime->movie) }}" class="transition hover:text-white">{{ $showtime->movie->title }}</a>
            <span>/</span>
            <span class="text-slate-300">Choose seats</span>
        </nav>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="eyebrow">Step 1 of 3</p>
                <h1 class="mt-2 font-display text-2xl font-semibold tracking-tight text-white sm:text-3xl">Pick your seats</h1>
                <p class="mt-2 text-sm text-slate-400">
                    {{ $showtime->movie->title }} &middot;
                    {{ $showtime->start_time->format('D j M, g:i A') }} &middot;
                    {{ $showtime->screen->name }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="badge badge-blue">1 Seats</span>
                <span class="badge badge-slate">2 Payment</span>
                <span class="badge badge-slate">3 Ticket</span>
            </div>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_21rem]">
            {{-- Seat map --}}
            <div class="card p-4 sm:p-7">
                <div class="mx-auto max-w-2xl">
                    <div class="relative mx-auto h-10 w-3/4">
                        <div class="absolute inset-x-0 top-0 h-1.5 rounded-full bg-gradient-to-r from-transparent via-brand-400/80 to-transparent"></div>
                        <div class="absolute inset-x-0 top-4 h-12 rounded-[100%] bg-brand-500/10 blur-2xl"></div>
                        <p class="absolute inset-x-0 top-1 text-center text-[10px] uppercase tracking-[.35em] text-slate-500">Screen</p>
                    </div>

                    <div class="mt-6 overflow-x-auto pb-2">
                        <div class="mx-auto flex w-max flex-col items-center gap-1.5 sm:gap-2">
                            @foreach ($seats->groupBy('row') as $row => $rowSeats)
                                <div class="flex items-center gap-1.5 sm:gap-2">
                                    <span class="w-5 text-center text-[11px] font-semibold text-slate-500">{{ $row }}</span>

                                    @foreach ($rowSeats as $seat)
                                        @php
                                            $isSold = in_array($seat->id, $soldSeats, true);
                                            $isHeld = in_array($seat->id, $otherHolds, true);
                                            $isSelected = in_array($seat->id, $selectedSeats, true);
                                            $price = PricingService::seatPriceCents($showtime, $seat);

                                            if ($isSold) {
                                                $staticClass = 'seat-sold';
                                                $staticLabel = 'Sold';
                                            } elseif ($isHeld) {
                                                $staticClass = 'seat-held';
                                                $staticLabel = 'Held by another customer';
                                            } elseif ($seat->is_disabled) {
                                                $staticClass = 'seat-disabled';
                                                $staticLabel = 'Not available';
                                            } elseif ($salesClosed) {
                                                $staticClass = ($seat->is_premium ? 'seat-premium' : 'seat-available').' cursor-not-allowed opacity-60';
                                                $staticLabel = 'Sales closed';
                                            } else {
                                                $staticClass = null;
                                                $staticLabel = null;
                                            }
                                        @endphp

                                        @if ($staticClass)
                                            <span class="seat {{ $staticClass }}"
                                                  aria-label="{{ $seat->label }} — {{ $staticLabel }}"
                                                  title="{{ $seat->label }} — {{ $staticLabel }}">
                                                {{ $isSold || $isHeld ? '×' : ($seat->is_disabled ? '' : $seat->number) }}
                                            </span>
                                        @else
                                            <button type="button"
                                                    data-seat
                                                    data-id="{{ $seat->id }}"
                                                    data-label="{{ $seat->label }}"
                                                    data-price="{{ $price }}"
                                                    data-type="{{ $seat->is_premium ? 'premium' : 'standard' }}"
                                                    aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                                                    aria-label="{{ $seat->label }} — {{ $seat->is_premium ? 'Premium' : 'Standard' }} — {{ PricingService::format($price) }}"
                                                    class="seat {{ $isSelected ? 'seat-selected' : ($seat->is_premium ? 'seat-premium' : 'seat-available') }}"
                                                    title="{{ $seat->label }} — {{ $seat->is_premium ? 'Premium' : 'Standard' }} — {{ PricingService::format($price) }}">
                                                {{ $seat->number }}
                                            </button>
                                        @endif

                                        @if ($seat->number === 6)
                                            <span class="w-3 sm:w-6"></span>
                                        @endif
                                    @endforeach

                                    <span class="w-5 text-center text-[11px] font-semibold text-slate-500">{{ $row }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-7 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 border-t border-white/[.06] pt-5 text-[11px] text-slate-500">
                        <span class="flex items-center gap-1.5"><span class="h-3.5 w-3.5 rounded-sm border border-white/15 bg-night-700"></span> Available</span>
                        <span class="flex items-center gap-1.5"><span class="h-3.5 w-3.5 rounded-sm border border-amber-400/30 bg-amber-500/15"></span> Premium</span>
                        <span class="flex items-center gap-1.5"><span class="h-3.5 w-3.5 rounded-sm bg-brand-600"></span> Selected</span>
                        <span class="flex items-center gap-1.5"><span class="h-3.5 w-3.5 rounded-sm bg-white/[.05]"></span> Sold</span>
                        <span class="flex items-center gap-1.5"><span class="h-3.5 w-3.5 rounded-sm border border-amber-400/20 bg-amber-500/10"></span> Held by another customer</span>
                    </div>
                </div>
            </div>

            {{-- Summary --}}
            <aside class="lg:sticky lg:top-24 lg:self-start">
                @if ($salesClosed)
                    <div class="card p-5">
                        <p class="eyebrow text-amber-400">Sales closed</p>
                        <h2 class="mt-2 font-display text-base font-semibold text-white">This show is no longer on sale</h2>
                        <p class="mt-2 text-sm leading-relaxed text-slate-400">
                            Tickets can only be bought up to {{ \App\Support\BookingRules::SALES_CUTOFF_MINUTES }} minutes before the start time.
                        </p>
                        <a href="{{ route('movies.show', $showtime->movie) }}" class="btn btn-ghost mt-5 w-full">Other showtimes &rarr;</a>
                    </div>
                @else
                    <form action="{{ auth()->check() ? route('booking.hold', $showtime) : route('login') }}"
                          method="{{ auth()->check() ? 'POST' : 'GET' }}"
                          class="card p-5">
                        @csrf
                        @guest
                            <input type="hidden" name="redirect" value="{{ request()->getRequestUri() }}">
                        @endguest
                        <div id="seat-inputs"></div>

                        <div class="flex items-center justify-between">
                            <h2 class="font-display text-base font-semibold text-white">Your selection</h2>
                            <span id="seat-count" class="badge badge-blue">0 seats</span>
                        </div>

                        <div id="empty-state" class="mt-4 rounded-xl border border-dashed border-white/10 px-4 py-8 text-center">
                            <p class="text-sm text-slate-500">Tap a seat to add it to your booking.</p>
                        </div>

                        <ul id="selected-list" class="mt-4 hidden space-y-2 text-sm"></ul>

                        <div class="mt-4 space-y-1.5 border-t border-white/[.07] pt-4 text-sm">
                            <div class="flex justify-between text-slate-400">
                                <span>Standard</span>
                                <span id="standard-total">{{ PricingService::format(0) }}</span>
                            </div>
                            <div class="flex justify-between text-slate-400">
                                <span>Premium</span>
                                <span id="premium-total">{{ PricingService::format(0) }}</span>
                            </div>
                            <div class="flex items-baseline justify-between pt-2">
                                <span class="font-medium text-white">Total</span>
                                <span id="grand-total" class="font-display text-xl font-semibold text-white">{{ PricingService::format(0) }}</span>
                            </div>
                        </div>

                        <p id="limit-note" class="mt-3 text-[11px] text-slate-500">You can reserve up to {{ $maxSeats }} seats per booking.</p>

                        @if ($holdExpiresAt)
                            <p class="mt-3 rounded-lg border border-amber-500/20 bg-amber-500/10 px-3 py-2 text-[11px] text-amber-200">
                                Your current hold expires in
                                <span id="hold-countdown" data-expires-at="{{ $holdExpiresAt->toIso8601String() }}" class="font-semibold">{{ $holdExpiresAt->format('g:i A') }}</span>.
                                Continuing replaces it.
                            </p>
                        @endif

                        @auth
                            <button id="continue-btn" type="submit" class="btn btn-primary mt-5 w-full" disabled>
                                Continue to payment
                            </button>
                        @else
                            <button id="continue-btn" type="submit" class="btn btn-primary mt-5 w-full" disabled>
                                Sign in to reserve
                            </button>
                        @endauth

                        <p class="mt-3 text-center text-[11px] leading-relaxed text-slate-500">
                            Seats are held for {{ \App\Services\SettingsService::get('hold_minutes', 10) }} minutes while you pay.
                            Sales close {{ \App\Support\BookingRules::SALES_CUTOFF_MINUTES }} minutes before the show.
                        </p>
                    </form>

                    @if ($holdExpiresAt)
                        <form method="POST" action="{{ route('booking.release', $showtime) }}" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-ghost w-full btn-sm">Release my seats</button>
                        </form>
                    @endif
                @endif
            </aside>
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const maxSeats = {{ (int) $maxSeats }};
                const selected = new Set(@json(array_map('intval', $selectedSeats)));
                const buttons = Array.from(document.querySelectorAll('[data-seat]'));
                const inputs = document.getElementById('seat-inputs');
                const countBadge = document.getElementById('seat-count');
                const emptyState = document.getElementById('empty-state');
                const list = document.getElementById('selected-list');
                const standardTotal = document.getElementById('standard-total');
                const premiumTotal = document.getElementById('premium-total');
                const grandTotal = document.getElementById('grand-total');
                const continueBtn = document.getElementById('continue-btn');
                const limitNote = document.getElementById('limit-note');

                // Sales closed or signed-out edge renders no booking form.
                if (!inputs || !countBadge || !continueBtn || !limitNote) {
                    return;
                }

                const money = (cents) => '$' + (cents / 100).toFixed(2);
                const byId = new Map(buttons.map((b) => [b.dataset.id, b]));

                function baseClass(type) {
                    return type === 'premium' ? 'seat-premium' : 'seat-available';
                }

                function render() {
                    let standard = 0;
                    let premium = 0;
                    let count = 0;

                    list.innerHTML = '';
                    inputs.innerHTML = '';

                    byId.forEach((button, id) => {
                        const isSelected = selected.has(Number(id));
                        button.classList.toggle('seat-selected', isSelected);
                        button.classList.toggle(baseClass(button.dataset.type), !isSelected);
                        button.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
                    });

                    selected.forEach((id) => {
                        const button = byId.get(String(id));
                        if (!button) return;

                        const price = Number(button.dataset.price);
                        const type = button.dataset.type;
                        count++;

                        if (type === 'premium') premium += price;
                        else standard += price;

                        const li = document.createElement('li');
                        li.className = 'flex items-center justify-between gap-2 rounded-lg border border-white/[.07] bg-white/[.02] px-3 py-2';
                        li.innerHTML = `
                            <span class="flex items-center gap-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-brand-600/20 text-[10px] font-bold text-brand-300">${button.dataset.label}</span>
                                <span class="text-xs text-slate-400">${type === 'premium' ? 'Premium' : 'Standard'}</span>
                            </span>
                            <span class="text-xs font-semibold text-white">${money(price)}</span>`;
                        list.appendChild(li);

                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'seat_ids[]';
                        input.value = id;
                        inputs.appendChild(input);
                    });

                    standardTotal.textContent = money(standard);
                    premiumTotal.textContent = money(premium);
                    grandTotal.textContent = money(standard + premium);
                    countBadge.textContent = count + (count === 1 ? ' seat' : ' seats');

                    emptyState.classList.toggle('hidden', count > 0);
                    list.classList.toggle('hidden', count === 0);
                    continueBtn.disabled = count === 0;

                    limitNote.textContent = count >= maxSeats
                        ? `Maximum of ${maxSeats} seats reached.`
                        : `You can reserve up to ${maxSeats} seats per booking.`;
                    limitNote.classList.toggle('text-amber-300', count >= maxSeats);
                }

                byId.forEach((button, id) => {
                    button.addEventListener('click', () => {
                        const numericId = Number(id);

                        if (selected.has(numericId)) {
                            selected.delete(numericId);
                        } else if (selected.size >= maxSeats) {
                            limitNote.textContent = `You can only select up to ${maxSeats} seats.`;
                            limitNote.classList.add('text-amber-300');
                            return;
                        } else {
                            selected.add(numericId);
                        }

                        render();
                    });
                });

                render();

                const countdown = document.getElementById('hold-countdown');

                if (countdown) {
                    const expiresAt = new Date(countdown.dataset.expiresAt).getTime();

                    const paintCountdown = () => {
                        const left = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000));

                        if (left === 0) {
                            countdown.textContent = 'expired';
                            countdown.classList.add('text-red-300');
                            continueBtn.disabled = true;

                            return false;
                        }

                        const minutes = Math.floor(left / 60);
                        const seconds = String(left % 60).padStart(2, '0');
                        countdown.textContent = `${minutes}:${seconds}`;

                        return true;
                    };

                    if (paintCountdown()) {
                        const timer = setInterval(() => {
                            if (!paintCountdown()) clearInterval(timer);
                        }, 1000);
                    }
                }
            })();
        </script>
    @endpush
</x-layouts.public>
