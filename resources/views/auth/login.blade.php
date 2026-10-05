<x-guest-layout>
    <div class="mb-6"><h1 class="text-2xl font-black text-slate-900">Masuk ke workspace</h1><p class="mt-1 text-sm text-slate-500">Lanjutkan pekerjaan tim dari sini.</p></div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1 block min-h-11 w-full rounded-xl text-base" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Kata sandi" />

            <x-text-input id="password" class="mt-1 block min-h-11 w-full rounded-xl text-base"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="mt-4">
            <label for="remember_me" class="inline-flex min-h-11 items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-cyan-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
            </label>
        </div>

        <div class="mt-5 flex flex-col gap-4">
            <x-primary-button class="min-h-12 w-full justify-center rounded-xl text-sm normal-case tracking-normal">Masuk</x-primary-button>
            @if (Route::has('password.request'))
                <a class="text-center text-sm font-semibold text-blue-700 hover:text-blue-900 focus:outline-none focus:ring-2 focus:ring-cyan-300 focus:ring-offset-2" href="{{ route('password.request') }}">
                    Lupa kata sandi?
                </a>
            @endif
        </div>
    </form>
</x-guest-layout>
