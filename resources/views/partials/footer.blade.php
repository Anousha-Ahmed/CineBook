<footer class="mt-24 border-t border-white/[.06] bg-night-950">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-4 lg:px-8">
        <div class="md:col-span-1">
            <a href="{{ route('home') }}" class="font-display text-lg font-bold tracking-tight text-white">
                Cine<span class="text-brand-500">Book</span>
            </a>
            <p class="mt-3 max-w-xs text-sm leading-relaxed text-slate-500">
                One cinema. Extraordinary worlds. Book your seat in seconds and walk in with your ticket already in hand.
            </p>
        </div>

        <div>
            <h4 class="text-xs font-semibold uppercase tracking-[.18em] text-slate-400">Explore</h4>
            <ul class="mt-4 space-y-2.5 text-sm text-slate-500">
                <li><a href="{{ route('movies.index') }}" class="transition hover:text-white">All movies</a></li>
                <li><a href="{{ route('movies.index', ['filter' => 'now-showing']) }}" class="transition hover:text-white">Now showing</a></li>
                @if (! auth()->check() || auth()->user()->role !== 'staff')
                    <li><a href="{{ route('bookings.index') }}" class="transition hover:text-white">My bookings</a></li>
                @endif
            </ul>
        </div>

        <div>
            <h4 class="text-xs font-semibold uppercase tracking-[.18em] text-slate-400">Visit us</h4>
            <ul class="mt-4 space-y-2.5 text-sm text-slate-500">
                <li>120 Market Street, Downtown</li>
                <li>Doors open 30 minutes before show</li>
                <li>Ticket scanning closes 30 minutes after start</li>
            </ul>
        </div>

        <div>
            <h4 class="text-xs font-semibold uppercase tracking-[.18em] text-slate-400">Plan with confidence</h4>
            <ul class="mt-4 space-y-2.5 text-sm text-slate-500">
                <li>Reserve up to {{ \App\Services\SettingsService::get('max_seats', 6) }} seats per booking</li>
                <li>Cancel up to {{ \App\Services\SettingsService::get('cancel_cutoff_hours', 2) }} hours before showtime</li>
                <li>Secure checkout powered by Stripe</li>
            </ul>
        </div>
    </div>

    <div class="border-t border-white/[.06]">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-5 text-xs text-slate-600 sm:flex-row sm:px-6 lg:px-8">
            <p>&copy; {{ date('Y') }} CineBook Downtown. All rights reserved.</p>
            <p>USD &middot; Stripe test mode &middot; No real money is charged.</p>
        </div>
    </div>
</footer>
