<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'IniBisa' }}</title>
    <x-pwa-head />
    <script>if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#f6fbff] text-slate-800" x-data="{ moreOpen: false }" @keydown.escape.window="moreOpen = false">
    <header class="workspace-header sticky top-0 z-20 border-b border-cyan-100 bg-white/95 backdrop-blur">
        <div class="grid h-16 grid-cols-[1fr_auto] items-center gap-3 px-4 sm:px-5 lg:grid-cols-[1fr_auto_1fr] xl:px-9">
            <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-2"><x-application-logo
                    class="h-11 w-11 rounded-xl object-contain" /><span
                    class="text-base font-black tracking-tight text-blue-950 sm:text-lg">IniBisa</span></a>
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
                <button type="button" data-install-app hidden class="desktop-install-button hidden lg:inline-flex" aria-label="Pasang aplikasi" title="Pasang IniBisa"><i data-lucide="download" class="h-5 w-5"></i><span class="hidden xl:inline">Pasang aplikasi</span></button>
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
                <button type="button" @click="open = !open" @click.outside="open = false" class="flex min-h-11 items-center gap-2 rounded-xl px-1 py-1.5 text-left transition hover:bg-cyan-50 sm:px-2" aria-label="Menu akun" :aria-expanded="open.toString()">
                    <span class="grid h-8 w-8 place-items-center rounded-full bg-cyan-100 text-sm font-black text-blue-800">{{ str(auth()->user()->name)->substr(0, 1) }}</span>
                    <span class="hidden sm:block"><span class="block text-sm font-bold leading-4">{{ auth()->user()->name }}</span><span class="block text-xs text-slate-400">{{ auth()->user()->position ?: auth()->user()->role }}</span></span><span class="hidden text-xs text-slate-400 sm:inline">⌄</span>
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
        <div class="flex items-center justify-between px-4 pt-5 sm:px-5 xl:px-9">
            <div>
                <h1 class="text-2xl font-black">{{ $heading ?? 'Dasbor' }}</h1>
            </div>
        </div>
        @if (session('success') || session('error'))
            <div id="workspace-flash" data-type="{{ session('error') ? 'error' : 'success' }}" data-message="{{ session('error') ?: session('success') }}" hidden></div>
        @endif
        <div class="workspace-content p-4 pb-28 sm:p-5 xl:p-9">{{ $slot }}</div>
    </main>
    <aside data-install-offer hidden class="install-offer fixed inset-x-4 bottom-28 z-50 rounded-2xl border border-blue-100 bg-white p-4 shadow-2xl shadow-blue-950/20 lg:inset-x-auto lg:bottom-6 lg:right-6 lg:w-96" role="region" aria-labelledby="install-offer-title">
        <div class="flex items-start gap-3">
            <img src="/icons/pwa-192.png" alt="" class="h-12 w-12 rounded-xl border border-slate-100 object-cover">
            <div class="min-w-0 flex-1"><h2 id="install-offer-title" class="text-base font-black">Pasang IniBisa?</h2><p class="mt-1 text-sm leading-5 text-slate-500">Buka tugas dan produk langsung dari layar utama.</p></div>
            <button type="button" data-install-offer-close class="grid h-8 w-8 shrink-0 place-items-center rounded-lg text-xl text-slate-400 hover:bg-slate-100" aria-label="Tutup ajakan instal">×</button>
        </div>
        <div class="mt-4 flex gap-2"><button type="button" data-install-offer-action class="brand-button min-h-11 flex-1">Pasang aplikasi</button><button type="button" data-install-offer-close class="action-button min-h-11 px-4">Nanti</button></div>
    </aside>
    <div x-cloak x-show="moreOpen" x-transition.opacity class="fixed inset-0 z-30 bg-slate-950/40 lg:hidden" @click="moreOpen = false"></div>
    <section x-cloak x-show="moreOpen" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full" class="mobile-more-sheet fixed inset-x-0 bottom-0 z-40 rounded-t-3xl bg-white p-4 shadow-2xl lg:hidden" aria-label="Menu lainnya">
        <div class="mb-4 flex items-center justify-between"><h2 class="text-lg font-black">Menu lainnya</h2><button type="button" @click="moreOpen = false" class="grid h-11 w-11 place-items-center rounded-xl bg-slate-100" aria-label="Tutup menu">×</button></div>
        <div class="grid grid-cols-2 gap-2">
            <a class="mobile-more-link" href="{{ route('audiences') }}"><i data-lucide="users-round"></i>Audiens</a>
            <a class="mobile-more-link" href="{{ route('social-media-accounts') }}"><i data-lucide="at-sign"></i>Sosial Media</a>
            <a class="mobile-more-link" href="{{ route('team') }}"><i data-lucide="user-round"></i>Tim</a>
            <a class="mobile-more-link" href="{{ route('profile.edit') }}"><i data-lucide="settings-2"></i>Akun saya</a>
        </div>
        <button type="button" data-install-app hidden class="mobile-install-button mt-3"><i data-lucide="download"></i><span>Pasang IniBisa<span class="block text-xs font-medium opacity-80">Buka langsung dari layar utama</span></span></button>
    </section>
    <nav class="mobile-bottom-nav fixed z-20 grid grid-cols-5 gap-1 rounded-[1.4rem] border border-slate-200 bg-white/95 p-1.5 shadow-xl shadow-slate-900/10 backdrop-blur lg:hidden" aria-label="Navigasi utama">
        <a class="mobile-nav {{ request()->routeIs('dashboard') ? 'mobile-nav-active' : '' }}" href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif><span class="mobile-nav-icon"><i data-lucide="house"></i></span><span class="mobile-nav-label">Beranda</span></a>
        <a class="mobile-nav {{ request()->routeIs('tasks*') ? 'mobile-nav-active' : '' }}" href="{{ route('tasks') }}" @if(request()->routeIs('tasks*')) aria-current="page" @endif><span class="mobile-nav-icon"><i data-lucide="list-todo"></i></span><span class="mobile-nav-label">Tugas</span></a>
        <a class="mobile-nav {{ request()->routeIs('products*') ? 'mobile-nav-active' : '' }}" href="{{ route('products') }}" @if(request()->routeIs('products*')) aria-current="page" @endif><span class="mobile-nav-icon"><i data-lucide="package"></i></span><span class="mobile-nav-label">Produk</span></a>
        <a class="mobile-nav {{ request()->routeIs('ideas*') ? 'mobile-nav-active' : '' }}" href="{{ route('ideas') }}" @if(request()->routeIs('ideas*')) aria-current="page" @endif><span class="mobile-nav-icon"><i data-lucide="lightbulb"></i></span><span class="mobile-nav-label">Ide</span></a>
        <button type="button" class="mobile-nav {{ request()->routeIs('audiences*', 'social-media-accounts*', 'team', 'profile.*') ? 'mobile-nav-active' : '' }}" @click="moreOpen = true" :aria-expanded="moreOpen.toString()" aria-label="Buka menu lainnya"><span class="mobile-nav-icon"><i data-lucide="menu"></i></span><span class="mobile-nav-label">Lainnya</span></button>
    </nav>
</body>

</html>
