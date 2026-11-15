<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'CineBook') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|sora:500,600,700" rel="stylesheet" />

        <style>[x-cloak]{display:none!important}</style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-night-950 font-sans text-slate-300 antialiased">
        @include('partials.nav')
        @include('partials.flash')

        @isset($header)
            <div class="border-b border-white/[.06] bg-night-900/40">
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </div>
        @endisset

        <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>
    </body>
</html>
