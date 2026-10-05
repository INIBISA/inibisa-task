<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <x-pwa-head />

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased">
        <div class="guest-shell flex min-h-screen flex-col items-center justify-center bg-[#f6fbff] px-4 py-8">
            <a href="/" class="mb-6 flex items-center gap-3" aria-label="IniBisa beranda">
                <x-application-logo class="h-16 w-16 object-contain" />
                <span class="text-2xl font-black tracking-tight text-blue-950">IniBisa</span>
            </a>
            <div class="w-full max-w-md rounded-3xl border border-cyan-100 bg-white px-6 py-7 shadow-xl shadow-blue-950/5 sm:px-8">
                {{ $slot }}
            </div>
            <button type="button" data-install-app hidden class="mt-5 rounded-xl px-4 py-3 text-sm font-bold text-blue-700 underline underline-offset-4">Pasang IniBisa di layar utama</button>
        </div>
    </body>
</html>
