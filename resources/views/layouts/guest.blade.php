<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Sign in — CineBook' }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|sora:500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-night-950 font-sans text-slate-300 antialiased">
        <div class="grid min-h-screen lg:grid-cols-2">
            <aside class="relative hidden overflow-hidden border-r border-white/[.06] lg:flex lg:flex-col lg:justify-between lg:p-12">
                <div class="absolute inset-0 bg-grid-lines bg-[length:22px_22px] opacity-60"></div>
                <div class="absolute -left-24 top-1/3 h-72 w-72 rounded-full bg-brand-600/25 blur-[100px]"></div>
                <div class="absolute -right-16 bottom-10 h-72 w-72 rounded-full bg-indigo-600/20 blur-[110px]"></div>

                <a href="{{ route('home') }}" class="relative z-10 font-display text-lg font-bold tracking-tight text-white">
                    Cine<span class="text-brand-500">Book</span>
                </a>

                <div class="relative z-10 max-w-md">
                    <p class="eyebrow">Downtown cinema</p>
                    <h1 class="mt-4 font-display text-4xl font-semibold leading-tight tracking-tight text-white text-balance">
                        Your seat is waiting. Book it in under a minute.
                    </h1>
                    <p class="mt-4 text-sm leading-relaxed text-slate-400">
                        Pick a showtime, choose seats from a live map, and pay securely. Your ticket — with a scannable code — is ready the moment you check out.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-2">
                        <span class="chip">Two screens</span>
                        <span class="chip">Standard &amp; premium seats</span>
                        <span class="chip">Instant e-tickets</span>
                    </div>
                </div>

                <p class="relative z-10 text-xs text-slate-600">120 Market Street &middot; Doors open 30 min before show</p>
            </aside>

            <main class="flex items-center justify-center px-4 py-12 sm:px-8">
                <div class="w-full max-w-md">
                    <a href="{{ route('home') }}" class="mb-8 flex items-center justify-center gap-2 lg:hidden">
                        <span class="font-display text-lg font-bold tracking-tight text-white">Cine<span class="text-brand-500">Book</span></span>
                    </a>

                    <div class="card p-7 sm:p-9">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>
