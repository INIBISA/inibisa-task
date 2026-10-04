<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'IniBisa' }}</title><script>if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');</script>@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#f6fbff] text-slate-800">
    <header class="sticky top-0 z-20 border-b border-cyan-100 bg-white/95 backdrop-blur">
        <div class="grid h-16 grid-cols-[1fr_auto_1fr] items-center gap-5 px-5 xl:px-9">
            <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-2"><x-application-logo
                    class="h-11 w-11 rounded-xl object-contain" /><span
                    class="hidden text-lg font-black tracking-tight sm:block">IniBisa</span></a>
            <nav class="hidden items-center justify-center gap-1 lg:flex">
                <a class="top-nav {{ request()->routeIs('dashboard') ? 'top-nav-active' : '' }}"
                    href="{{ route('dashboard') }}">Dasbor</a>
                <a class="top-nav {{ request()->routeIs('tasks*') ? 'top-nav-active' : '' }}"
                    href="{{ route('tasks') }}">Tugas</a>
                <a class="top-nav {{ request()->routeIs('products*') ? 'top-nav-active' : '' }}"
                    href="{{ route('products') }}">Produk</a>
                <a class="top-nav {{ request()->routeIs('ideas*') ? 'top-nav-active' : '' }}"
                    href="{{ route('ideas') }}">Ide</a>
                <a class="top-nav {{ request()->routeIs('audiences*') ? 'top-nav-active' : '' }}"
                    href="{{ route('audiences') }}">Audiens</a>
                <a class="top-nav {{ request()->routeIs('social-media-accounts*') ? 'top-nav-active' : '' }}"
                    href="{{ route('social-media-accounts') }}">Sosial Media</a>
                <a class="top-nav {{ request()->routeIs('team') ? 'top-nav-active' : '' }}"
                    href="{{ route('team') }}">Tim</a>
            </nav>
            <div class="flex items-center justify-end gap-2" x-data="{ open: false }">
                <button type="button" @click="$store.theme.toggle()" class="theme-toggle" :aria-label="$store.theme.dark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap'" :title="$store.theme.dark ? 'Mode terang' : 'Mode gelap'">
                    <span class="theme-toggle-sun" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                            <path d="M12 4V2M12 22v-2M4 12H2M22 12h-2M5.64 5.64 4.22 4.22M19.78 19.78l-1.42-1.42M18.36 5.64l1.42-1.42M4.22 19.78l1.42-1.42" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </span>
                    <span class="theme-toggle-moon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                            <path d="M20.5 14.5A7.8 7.8 0 0 1 9.5 3.5 8.6 8.6 0 1 0 20.5 14.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </button>
                <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-2 rounded-xl px-2 py-1.5 text-left transition hover:bg-cyan-50" aria-label="Menu akun">
                    <span class="grid h-8 w-8 place-items-center rounded-full bg-cyan-100 text-sm font-black text-blue-800">{{ str(auth()->user()->name)->substr(0, 1) }}</span>
                    <span class="hidden sm:block"><span class="block text-sm font-bold leading-4">{{ auth()->user()->name }}</span><span class="block text-xs text-slate-400">{{ auth()->user()->position ?: auth()->user()->role }}</span></span><span class="text-xs text-slate-400">⌄</span>
                </button>
                <div x-cloak x-show="open" x-transition class="absolute right-5 top-14 z-30 w-56 rounded-2xl border border-cyan-100 bg-white p-2 shadow-xl xl:right-9">
                    <a href="{{ route('profile.edit') }}" class="account-menu">Pengaturan akun</a>
                    <div class="my-1 border-t border-slate-100"></div>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="account-menu w-full text-rose-600 hover:bg-rose-50">Keluar</button></form>
                </div>
            </div>
        </div>
    </header>
    <main>
        <div class="flex items-center justify-between px-5 pt-6 xl:px-9">
            <div>
                <h1 class="text-2xl font-black">{{ $heading ?? 'Dasbor' }}</h1>
            </div>
        </div>
        @if (session('success') || session('error'))
            <div id="workspace-flash" data-type="{{ session('error') ? 'error' : 'success' }}" data-message="{{ session('error') ?: session('success') }}" hidden></div>
        @endif
        <div class="p-5 pb-24 xl:p-9">{{ $slot }}</div>
    </main>
    <nav
        class="fixed inset-x-0 bottom-0 z-20 flex justify-around border-t border-slate-200 bg-white/95 px-2 py-2 backdrop-blur lg:hidden">
        <a class="mobile-nav" href="{{ route('dashboard') }}">◌<span>Beranda</span></a><a class="mobile-nav"
            href="{{ route('tasks') }}">✓<span>Tugas</span></a><a class="mobile-nav"
            href="{{ route('products') }}">□<span>Produk</span></a><a class="mobile-nav"
            href="{{ route('ideas') }}">✦<span>Ide</span></a></nav>
</body>

</html>
