<x-workspace heading="Akun Sosial Media">
    @php($platforms = ['Instagram' => ['icon' => 'camera', 'color' => 'bg-pink-100 text-pink-700'], 'TikTok' => ['icon' => 'music-2', 'color' => 'bg-slate-900 text-white'], 'YouTube' => ['icon' => 'video', 'color' => 'bg-red-100 text-red-700'], 'Facebook' => ['icon' => 'thumbs-up', 'color' => 'bg-blue-100 text-blue-700'], 'X' => ['icon' => 'at-sign', 'color' => 'bg-slate-800 text-white'], 'LinkedIn' => ['icon' => 'briefcase-business', 'color' => 'bg-sky-100 text-sky-700']])
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3"><p class="text-sm text-slate-500">Pilih platform, lalu isi username dan data login. Tautan profil dibuat otomatis.</p><button type="button" onclick="document.getElementById('social-media-account-form').showModal()" class="brand-button inline-flex items-center gap-2"><i data-lucide="plus" class="h-4 w-4"></i> Tambah akun</button></div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($accounts as $account)
            @php($platform = $platforms[$account->platform] ?? ['icon' => 'at-sign', 'color' => 'bg-violet-100 text-violet-700'])
            <article class="card entity-card {{ !$account->is_active ? 'opacity-70' : '' }}">
                <div class="flex items-start justify-between gap-3"><span class="badge inline-flex items-center gap-1.5 {{ $platform['color'] }}"><i data-lucide="{{ $platform['icon'] }}" class="h-3.5 w-3.5"></i>{{ $account->platform }}</span><span class="badge {{ $account->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</span></div>
                <h2 class="mt-4 truncate text-base font-bold text-slate-800">@{{ ltrim($account->name, '@') }}</h2>
                <a href="{{ $account->url }}" target="_blank" rel="noopener noreferrer" class="mt-2 block truncate text-sm text-cyan-700 underline">Buka profil</a>
                <div class="mt-5 border-t border-slate-100 pt-4 text-sm"><p class="text-slate-400">Email / login</p><p class="mt-1 truncate font-semibold text-slate-700">{{ $account->login_email ?: 'Belum diisi' }}</p></div>
                <div class="card-actions mt-5"><button type="button" onclick="document.getElementById('social-media-account-{{ $account->id }}').showModal()" class="action-button"><i data-lucide="pencil"></i> Edit</button>@if($account->is_active)<form method="POST" action="{{ route('social-media-accounts.deactivate', $account) }}" data-confirm-title="Nonaktifkan akun ini?">@csrf @method('PATCH')<button class="action-button"><i data-lucide="circle-off"></i> Nonaktifkan</button></form>@endif<form method="POST" action="{{ route('social-media-accounts.destroy', $account) }}" data-confirm-title="Hapus akun ini?" data-confirm-text="Data login dan sandi terenkripsi akan dihapus permanen." data-confirm-kind="danger">@csrf @method('DELETE')<button class="action-button action-danger"><i data-lucide="trash-2"></i> Hapus</button></form></div>
            </article>
            <dialog id="social-media-account-{{ $account->id }}" class="modal"><form method="POST" action="{{ route('social-media-accounts.update', $account) }}" class="space-y-3" data-confirm-title="Simpan perubahan akun?">@csrf @method('PUT')
                <div class="flex items-center justify-between"><h2 class="text-xl font-black">Edit akun</h2><button type="button" class="modal-close" onclick="this.closest('dialog').close()" aria-label="Tutup">×</button></div>
                <label class="block text-sm font-semibold text-slate-700">Platform<select class="field mt-1 w-full" required name="platform">@foreach($platforms as $name => $details)<option value="{{ $name }}" @selected($account->platform === $name)>{{ $name }}</option>@endforeach</select></label>
                <label class="block text-sm font-semibold text-slate-700">Username<input class="field mt-1 w-full" required name="name" value="{{ ltrim($account->name, '@') }}" placeholder="namatim" autocomplete="username"></label>
                <input class="field w-full" type="email" name="login_email" value="{{ $account->login_email }}" placeholder="Email / login" autocomplete="email">
                <input class="field w-full" type="password" name="password" placeholder="Kata sandi baru" autocomplete="new-password"><p class="text-xs text-amber-700">Sandi tersimpan terenkripsi. Kosongkan untuk mempertahankan sandi saat ini.</p>
                <button class="brand-button-dark">Simpan perubahan</button>
            </form></dialog>
        @empty
            <div class="card text-sm text-slate-500">Belum ada akun sosial media.</div>
        @endforelse
    </div>
    <dialog id="social-media-account-form" class="modal"><form method="POST" action="{{ route('social-media-accounts.store') }}" class="space-y-3">@csrf
        <div class="flex items-center justify-between"><h2 class="text-xl font-black">Tambah akun</h2><button type="button" class="modal-close" onclick="this.closest('dialog').close()" aria-label="Tutup">×</button></div>
        <label class="block text-sm font-semibold text-slate-700">Platform<select class="field mt-1 w-full" required name="platform">@foreach($platforms as $name => $details)<option value="{{ $name }}">{{ $name }}</option>@endforeach</select></label>
        <label class="block text-sm font-semibold text-slate-700">Username<input class="field mt-1 w-full" required name="name" placeholder="namatim" autocomplete="username"></label>
        <input class="field w-full" type="email" name="login_email" placeholder="Email / login" autocomplete="email">
        <input class="field w-full" type="password" name="password" placeholder="Kata sandi" autocomplete="new-password">
        <button class="brand-button-dark">Tambah akun</button>
    </form></dialog>
</x-workspace>
