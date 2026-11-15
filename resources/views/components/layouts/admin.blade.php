@php
    $nav = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'icon' => 'grid'],
        ['label' => 'Movies', 'route' => 'admin.movies.index', 'pattern' => 'admin.movies.*', 'icon' => 'film'],
        ['label' => 'Screens', 'route' => 'admin.screens.index', 'pattern' => 'admin.screens.*', 'icon' => 'screen'],
        ['label' => 'Showtimes', 'route' => 'admin.showtimes.index', 'pattern' => 'admin.showtimes.*', 'icon' => 'clock'],
        ['label' => 'Bookings', 'route' => 'admin.bookings.index', 'pattern' => 'admin.bookings.*', 'icon' => 'ticket'],
        ['label' => 'Customers', 'route' => 'admin.customers.index', 'pattern' => 'admin.customers.*', 'icon' => 'users'],
        ['label' => 'Genres', 'route' => 'admin.genres.index', 'pattern' => 'admin.genres.*', 'icon' => 'tag'],
        ['label' => 'Reports', 'route' => 'admin.reports.index', 'pattern' => 'admin.reports.*', 'icon' => 'chart'],
        ['label' => 'Audit log', 'route' => 'admin.audit.index', 'pattern' => 'admin.audit.*', 'icon' => 'history'],
        ['label' => 'Failed jobs', 'route' => 'admin.failed-jobs.index', 'pattern' => 'admin.failed-jobs.*', 'icon' => 'alert'],
        ['label' => 'Settings', 'route' => 'admin.settings.edit', 'pattern' => 'admin.settings.*', 'icon' => 'cog'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Admin — CineBook' }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|sora:500,600,700" rel="stylesheet" />

        <style>[x-cloak]{display:none!important}</style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-night-950 font-sans text-slate-300 antialiased">
        <div x-data="{ sidebar: false }" class="min-h-screen lg:grid lg:grid-cols-[16.5rem_minmax(0,1fr)]">
            <div x-show="sidebar" x-cloak @click="sidebar = false" class="fixed inset-0 z-30 bg-black/70 backdrop-blur-sm lg:hidden"></div>

            <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                   class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-white/[.06] bg-night-900 px-4 py-6 transition-transform duration-200 lg:static lg:z-auto lg:w-auto">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600/15 ring-1 ring-inset ring-brand-500/30">
                        <svg class="h-5 w-5 text-brand-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="16" rx="2.5" />
                            <path d="M7 4v16M17 4v16M3 9h4M3 15h4M17 9h4M17 15h4" />
                        </svg>
                    </span>
                    <div>
                        <p class="font-display text-base font-bold leading-none text-white">Cine<span class="text-brand-500">Book</span></p>
                        <p class="mt-1 text-[10px] uppercase tracking-[.2em] text-slate-500">Admin panel</p>
                    </div>
                </a>

                <nav class="mt-8 flex-1 space-y-1">
                    @foreach ($nav as $item)
                        @php
                            $active = request()->routeIs($item['pattern']);
                        @endphp
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ $active ? 'bg-brand-600/15 text-white ring-1 ring-inset ring-brand-500/25' : 'text-slate-400 hover:bg-white/[.04] hover:text-white' }}">
                            <svg class="{{ $active ? 'text-brand-400' : 'text-slate-500' }}" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                @switch($item['icon'])
                                    @case('grid')
                                        <rect x="3" y="3" width="7" height="7" rx="1.5" /><rect x="14" y="3" width="7" height="7" rx="1.5" /><rect x="3" y="14" width="7" height="7" rx="1.5" /><rect x="14" y="14" width="7" height="7" rx="1.5" />
                                        @break
                                    @case('film')
                                        <rect x="3" y="4" width="18" height="16" rx="2" /><path d="M7 4v16M17 4v16M3 9h4M3 15h4M17 9h4M17 15h4" />
                                        @break
                                    @case('clock')
                                        <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
                                        @break
                                    @case('ticket')
                                        <path d="M4 8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4V8Z" /><path d="M12 6v12" stroke-dasharray="2 3" />
                                        @break
                                    @case('screen')
                                        <rect x="3" y="4" width="18" height="12" rx="1.5" /><path d="M8 20h8M12 16v4" />
                                        @break
                                    @case('users')
                                        <circle cx="9" cy="8" r="3.2" /><path d="M3 20a6 6 0 0 1 12 0" /><path d="M16 5.5a3 3 0 0 1 0 5.8M17.5 14a5 5 0 0 1 3.5 6" />
                                        @break
                                    @case('tag')
                                        <path d="M3 12V5a2 2 0 0 1 2-2h7l9 9-9 9-9-9Z" /><circle cx="8" cy="8" r="1.3" />
                                        @break
                                    @case('chart')
                                        <path d="M4 20h16M7 16v-4M12 16V6M17 16v-7" />
                                        @break
                                    @case('history')
                                        <path d="M3 12a9 9 0 1 0 3-6.7" /><path d="M3 4v5h5M12 8v4l3 2" />
                                        @break
                                    @case('alert')
                                        <path d="M12 9v4m0 4h.01" stroke-linecap="round" /><path d="M10.3 4.3 2.6 18a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z" />
                                        @break
                                    @default
                                        <circle cx="12" cy="12" r="3" /><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.7 1.7 0 0 0-1.87-.34 1.7 1.7 0 0 0-1 1.55V21a2 2 0 1 1-4 0v-.09a1.7 1.7 0 0 0-1.11-1.55 1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.55-1H3a2 2 0 1 1 0-4h.09A1.7 1.7 0 0 0 4.6 9a1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6h.09A1.7 1.7 0 0 0 10 3.05V3a2 2 0 1 1 4 0v.09a1.7 1.7 0 0 0 1 1.55 1.7 1.7 0 0 0 1.87-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9v.09a1.7 1.7 0 0 0 1.55 1H21a2 2 0 1 1 0 4h-.09a1.7 1.7 0 0 0-1.51 1Z" />
                                @endswitch
                            </svg>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="mt-6 space-y-1 border-t border-white/[.06] pt-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-400 transition hover:bg-white/[.04] hover:text-white">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11 12 3l9 8" /><path d="M5 10v10h14V10" /></svg>
                        View site
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm text-slate-400 transition hover:bg-white/[.04] hover:text-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3" /><path d="M10 17l5-5-5-5M15 12H3" /></svg>
                            Sign out
                        </button>
                    </form>
                </div>
            </aside>

            <div class="flex min-h-screen flex-col">
                <header class="sticky top-0 z-20 flex items-center justify-between gap-4 border-b border-white/[.06] bg-night-950/85 px-4 py-4 backdrop-blur-xl sm:px-6">
                    <div class="flex items-center gap-3">
                        <button type="button" @click="sidebar = ! sidebar" class="rounded-lg border border-white/10 p-2 text-slate-300 lg:hidden">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" /></svg>
                        </button>
                        <div>
                            <h1 class="font-display text-lg font-semibold text-white">{{ $heading ?? 'Dashboard' }}</h1>
                            @isset($subheading)
                                <p class="text-xs text-slate-500">{{ $subheading }}</p>
                            @endisset
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        @isset($actions)
                            {{ $actions }}
                        @endisset
                        <div class="flex items-center gap-2 rounded-xl border border-white/10 bg-white/[.03] py-1.5 pl-1.5 pr-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-600 text-[11px] font-bold text-white">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                            <span class="hidden text-sm text-slate-200 sm:block">{{ auth()->user()->name }}</span>
                        </div>
                    </div>
                </header>

                @include('partials.flash')

                <main class="flex-1 px-4 py-8 sm:px-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
