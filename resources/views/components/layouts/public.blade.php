<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'CineBook — Downtown Cinema' }}</title>
        <meta name="description" content="{{ $description ?? 'Book cinema tickets at CineBook Downtown. Choose your seats, pay securely, and walk in with your ticket in hand.' }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|sora:500,600,700" rel="stylesheet" />

        <style>[x-cloak]{display:none!important}</style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-night-950 font-sans text-slate-300 antialiased">
        @include('partials.nav')
        @include('partials.flash')

        <main class="min-h-[60vh]">
            {{ $slot }}
        </main>

        @include('partials.footer')

        @stack('scripts')
    </body>
</html>
