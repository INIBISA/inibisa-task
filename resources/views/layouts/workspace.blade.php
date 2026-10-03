<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'IniBisa' }}</title>@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#f7f8f4] text-slate-800">
    <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="flex h-16 items-center gap-5 px-5 xl:px-9">
            <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-2"><span
                    class="grid h-9 w-9 place-items-center rounded-xl bg-lime-300 text-sm font-black text-[#172419]">iB</span><span
                    class="hidden text-lg font-black tracking-tight sm:block">IniBisa</span></a>
            <nav class="hidden min-w-0 flex-1 items-center gap-1 lg:flex">
                <a class="top-nav {{ request()->routeIs('dashboard') ? 'top-nav-active' : '' }}"
                    href="{{ route('dashboard') }}">Overview</a>
                <a class="top-nav {{ request()->routeIs('tasks*') ? 'top-nav-active' : '' }}"
                    href="{{ route('tasks') }}">Tasks</a>
                <a class="top-nav {{ request()->routeIs('products*') ? 'top-nav-active' : '' }}"
                    href="{{ route('products') }}">Products</a>
                <a class="top-nav {{ request()->routeIs('ideas*') ? 'top-nav-active' : '' }}"
                    href="{{ route('ideas') }}">Ideas</a>
                <a class="top-nav {{ request()->routeIs('audiences*') ? 'top-nav-active' : '' }}"
                    href="{{ route('audiences') }}">Audiences</a>
                <a class="top-nav {{ request()->routeIs('team') ? 'top-nav-active' : '' }}"
                    href="{{ route('team') }}">Team</a>
            </nav>
            <div class="ml-auto flex items-center gap-3"><span
                    class="hidden rounded-full bg-lime-100 px-3 py-1 text-xs font-bold text-lime-800 xl:block">● Polling
                    active</span><a href="{{ route('tasks') }}"
                    class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white">+ Task</a>
                <form class="hidden lg:block" method="POST" action="{{ route('logout') }}">@csrf<button
                        class="text-xs font-bold text-slate-400 hover:text-slate-900">Keluar</button></form>
            </div>
        </div>
    </header>
    <main>
        <div class="flex items-center justify-between px-5 pt-6 xl:px-9">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-lime-700">IniBisa workspace</p>
                <h1 class="text-2xl font-black">{{ $heading ?? 'Overview' }}</h1>
            </div><span class="text-sm text-slate-400">{{ auth()->user()->name }}</span>
        </div>
        @if (session('success'))
            <div class="mx-5 mt-5 rounded-xl bg-lime-100 px-4 py-3 text-sm font-semibold text-lime-900 xl:mx-9">
                {{ session('success') }}</div>
        @endif
        <div class="p-5 pb-24 xl:p-9">{{ $slot }}</div>
    </main>
    <nav
        class="fixed inset-x-0 bottom-0 z-20 flex justify-around border-t border-slate-200 bg-white/95 px-2 py-2 backdrop-blur lg:hidden">
        <a class="mobile-nav" href="{{ route('dashboard') }}">◌<span>Home</span></a><a class="mobile-nav"
            href="{{ route('tasks') }}">✓<span>Tasks</span></a><a class="mobile-nav"
            href="{{ route('products') }}">□<span>Produk</span></a><a class="mobile-nav"
            href="{{ route('ideas') }}">✦<span>Ideas</span></a></nav>
</body>

</html>
