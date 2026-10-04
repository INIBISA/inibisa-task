<x-workspace heading="Tim">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3"><p class="text-sm text-slate-500">Anggota dan tugas yang sedang mereka kerjakan.</p>@if(auth()->user()->role === 'admin')<button type="button" onclick="document.getElementById('member-form').showModal()" class="brand-button inline-flex items-center gap-2"><i data-lucide="plus" class="h-4 w-4"></i> Tambah anggota</button>@endif</div>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach($members as $member)
            <article class="card entity-card member-card {{ !$member->is_active ? 'opacity-70' : '' }}">
                <div class="flex items-center gap-3"><div class="grid h-11 w-11 shrink-0 place-items-center rounded-lg bg-violet-50 font-bold text-violet-800">{{ str($member->name)->substr(0, 1) }}</div><div class="min-w-0 flex-1"><h2 class="truncate text-base font-bold text-slate-800">{{ $member->name }}</h2><p class="truncate text-xs text-slate-500">{{ $member->position ?: ($member->role === 'admin' ? 'Admin' : 'Anggota') }}</p></div><span class="badge">{{ $member->is_active ? 'Aktif' : 'Nonaktif' }}</span></div>
                <div class="mb-4 mt-5 border-t border-slate-100 pt-4"><p class="text-xs font-semibold text-violet-700">{{ $member->assigned_tasks_count }} tugas ditugaskan</p>@forelse($member->assignedTasks->take(3) as $task)<p class="mt-2 truncate text-sm text-slate-600">{{ $task->title }} <span class="text-slate-400">· {{ $task->product?->name ?? 'Tanpa produk' }}</span></p>@empty<p class="mt-2 text-sm text-slate-400">Belum ada penugasan.</p>@endforelse</div>
                @if(auth()->user()->role === 'admin')
                    <div class="card-actions">
                        <button type="button" onclick="document.getElementById('member-{{ $member->id }}').showModal()" class="action-button"><i data-lucide="pencil"></i> Edit</button>
                        @if(!$member->is(auth()->user()))
                            @if($member->is_active)<form method="POST" action="{{ route('team.deactivate', $member) }}" data-confirm-title="Nonaktifkan anggota ini?" data-confirm-text="Anggota tidak dapat masuk lagi." data-confirm-kind="danger">@csrf @method('PATCH')<button class="action-button"><i data-lucide="user-x"></i> Nonaktifkan</button></form>@endif
                            <form method="POST" action="{{ route('team.destroy', $member) }}" data-confirm-title="Hapus anggota ini?" data-confirm-text="Akun akan dihapus permanen. Anggota dengan tugas atau ide buatannya tidak dapat dihapus." data-confirm-kind="danger">@csrf @method('DELETE')<button class="action-button action-danger"><i data-lucide="trash-2"></i> Hapus</button></form>
                        @endif
                    </div>
                @endif
            </article>
            @if(auth()->user()->role === 'admin')
                <dialog id="member-{{ $member->id }}" class="modal"><form method="POST" action="{{ route('team.update', $member) }}" class="space-y-3" data-confirm-title="Simpan perubahan anggota?">@csrf @method('PUT')
                    <div class="flex items-center justify-between"><h2 class="text-xl font-black">Edit anggota</h2><button type="button" onclick="this.closest('dialog').close()" class="modal-close" aria-label="Tutup">×</button></div>
                    <input class="field w-full" required name="name" value="{{ $member->name }}" aria-label="Nama">
                    <input class="field w-full" required type="email" name="email" value="{{ $member->email }}" aria-label="Email">
                    <div class="grid grid-cols-2 gap-3"><select class="field" name="role" aria-label="Peran"><option value="member" @selected($member->role === 'member')>Anggota</option><option value="admin" @selected($member->role === 'admin')>Admin</option></select><input class="field" name="position" value="{{ $member->position }}" placeholder="Posisi"></div>
                    <input class="field w-full" type="password" name="password" placeholder="Kata sandi baru (opsional)">
                    <input class="field w-full" type="password" name="password_confirmation" placeholder="Konfirmasi kata sandi">
                    <button class="brand-button-dark">Simpan perubahan</button>
                </form></dialog>
            @endif
        @endforeach
    </div>
    @if(auth()->user()->role === 'admin')
        <dialog id="member-form" class="modal"><form method="POST" action="{{ route('team.store') }}" class="space-y-3">@csrf
            <div class="flex items-center justify-between"><h2 class="text-xl font-black">Tambah anggota</h2><button type="button" onclick="this.closest('dialog').close()" class="modal-close" aria-label="Tutup">×</button></div>
            <input class="field w-full" required name="name" placeholder="Nama">
            <input class="field w-full" required type="email" name="email" placeholder="Email">
            <div class="grid grid-cols-2 gap-3"><select class="field" name="role" aria-label="Peran"><option value="member">Anggota</option><option value="admin">Admin</option></select><input class="field" name="position" placeholder="Posisi"></div>
            <input class="field w-full" required type="password" name="password" placeholder="Kata sandi minimal 8 karakter">
            <input class="field w-full" required type="password" name="password_confirmation" placeholder="Konfirmasi kata sandi">
            <button class="brand-button-dark">Tambah anggota</button>
        </form></dialog>
    @endif
</x-workspace>
