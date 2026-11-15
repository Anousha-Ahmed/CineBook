@php
    $links = [
        ['label' => 'Movies', 'route' => 'movies.index', 'active' => request()->routeIs('movies.*')],
        ['label' => 'My bookings', 'route' => 'bookings.index', 'active' => request()->routeIs('bookings.*')],
    ];
@endphp

<header class="sticky top-0 z-40 border-b border-white/[.06] bg-night-950/85 backdrop-blur-xl">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="group flex items-center gap-2.5">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600/15 ring-1 ring-inset ring-brand-500/30 transition group-hover:bg-brand-600/25">
                <svg class="h-5 w-5 text-brand-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="16" rx="2.5" />
                    <path d="M7 4v16M17 4v16M3 9h4M3 15h4M17 9h4M17 15h4" />
                </svg>
            </span>
            <span class="font-display text-lg font-bold tracking-tight text-white">
            Cine<span class="text-brand-500">Book</span>
            </span>
        </a>

        <nav class="hidden items-center gap-1 md:flex">
            @foreach ($links as $link)
                @if (Route::has($link['route']))
                    <a href="{{ route($link['route']) }}"
                       class="rounded-lg px-3.5 py-2 text-sm font-medium transition {{ $link['active'] ? 'bg-white/[.06] text-white' : 'text-slate-400 hover:bg-white/[.04] hover:text-white' }}">
                        {{ $link['label'] }}
                    </a>
                @endif
            @endforeach

            <span class="mx-2 h-5 w-px bg-white/10"></span>

            <span class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm text-slate-400">
                <svg class="h-4 w-4 text-brand-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11Z" stroke-linejoin="round" />
                    <circle cx="12" cy="10" r="2.5" />
                </svg>
                CineBook Downtown
            </span>

            @auth
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-400 transition hover:text-white">Admin</a>
                @endif
                @if (auth()->user()->isStaff())
                    <a href="{{ route('staff.scan') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-400 transition hover:text-white">Scan</a>
                @endif
            @endauth
        </nav>

        <div class="flex items-center gap-2">
            @auth
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" @click="open = ! open"
                            class="flex items-center gap-2 rounded-xl border border-white/10 bg-white/[.03] py-1.5 pl-1.5 pr-3 text-sm text-slate-200 transition hover:border-white/25">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-600 text-[11px] font-bold text-white">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <span class="hidden max-w-[120px] truncate sm:block">{{ auth()->user()->name }}</span>
                        <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.3 7.3a1 1 0 0 1 1.4 0L10 10.6l3.3-3.3a1 1 0 1 1 1.4 1.4l-4 4a1 1 0 0 1-1.4 0l-4-4a1 1 0 0 1 0-1.4Z" clip-rule="evenodd" /></svg>
                    </button>

                    <div x-show="open" x-transition.origin.top.right x-cloak
                         class="absolute right-0 mt-2 w-56 overflow-hidden rounded-xl border border-white/10 bg-night-850 p-1.5 shadow-2xl">
                        <div class="px-3 py-2">
                            <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                        </div>
                        <a href="{{ route('bookings.index') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-300 hover:bg-white/[.05] hover:text-white">My bookings</a>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-300 hover:bg-white/[.05] hover:text-white">Profile settings</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-slate-300 hover:bg-white/[.05] hover:text-white">Sign out</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="hidden rounded-lg px-3.5 py-2 text-sm font-medium text-slate-300 transition hover:text-white sm:block">Sign in</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Get started</a>
            @endauth

            <button type="button" class="ml-1 rounded-lg border border-white/10 p-2 text-slate-300 md:hidden"
                    x-data @click="$dispatch('toggle-mobile-nav')">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" /></svg>
            </button>
        </div>
    </div>

    <div x-data="{ open: false }" @toggle-mobile-nav.window="open = ! open" x-show="open" x-cloak class="border-t border-white/[.06] bg-night-950/95 md:hidden">
        <div class="space-y-1 px-4 py-3">
            @foreach ($links as $link)
                @if (Route::has($link['route']))
                    <a href="{{ route($link['route']) }}" class="block rounded-lg px-3 py-2 text-sm text-slate-300 hover:bg-white/[.05] hover:text-white">{{ $link['label'] }}</a>
                @endif
            @endforeach
            @auth
                @if (auth()->user()->isStaff())
                    <a href="{{ route('staff.scan') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-300 hover:bg-white/[.05]">Scan tickets</a>
                @endif
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-300 hover:bg-white/[.05]">Admin panel</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-300 hover:bg-white/[.05]">Sign in</a>
            @endauth
        </div>
    </div>
</header>
