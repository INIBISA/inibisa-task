<x-workspace heading="Audiens">
    <div class="mb-6 flex justify-end"><button type="button" onclick="document.getElementById('audience-form').showModal()" class="brand-button inline-flex items-center gap-2"><i data-lucide="plus" class="h-4 w-4"></i> Audiens baru</button></div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($audiences as $audience)
            <article class="card entity-card audience-card {{ $audience->is_archived ? 'opacity-70' : '' }}">
                <div class="flex items-start justify-between"><span class="grid h-10 w-10 place-items-center rounded-lg bg-teal-50 text-xl text-teal-700">{{ $audience->icon ?: '○' }}</span>@if($audience->is_archived)<span class="badge">Diarsipkan</span>@endif</div>
                <h2 class="mt-4 text-base font-bold text-slate-800">{{ $audience->name }}</h2>
                <p class="mt-2 line-clamp-3 min-h-14 text-sm leading-5 text-slate-600">{{ $audience->description ?: 'Belum ada deskripsi.' }}</p>
                <p class="mb-4 mt-3 text-xs font-semibold text-teal-700">{{ $audience->products_count }} produk</p>
                <div class="card-actions">
                    <button type="button" onclick="document.getElementById('audience-{{ $audience->id }}').showModal()" class="action-button"><i data-lucide="pencil"></i> Edit</button>
                    @if(auth()->user()->role === 'admin')
                        @if(!$audience->is_archived)<form method="POST" action="{{ route('audiences.archive', $audience) }}" data-confirm-title="Arsipkan audiens ini?" data-confirm-text="Audiens tetap tersimpan dan dapat dilihat di daftar." data-confirm-kind="danger">@csrf @method('PATCH')<button class="action-button"><i data-lucide="archive"></i> Arsipkan</button></form>@endif
                        <form method="POST" action="{{ route('audiences.destroy', $audience) }}" data-confirm-title="Hapus audiens ini?" data-confirm-text="Audiens dihapus permanen. Produk yang terkait tetap ada tanpa audiens." data-confirm-kind="danger">@csrf @method('DELETE')<button class="action-button action-danger"><i data-lucide="trash-2"></i> Hapus</button></form>
                    @endif
                </div>
            </article>
            <dialog id="audience-{{ $audience->id }}" class="modal"><form method="POST" action="{{ route('audiences.update', $audience) }}" class="space-y-3" data-confirm-title="Simpan perubahan audiens?">@csrf @method('PUT')
                <div class="flex items-center justify-between"><h2 class="text-xl font-black">Edit audiens</h2><button type="button" class="modal-close" onclick="this.closest('dialog').close()" aria-label="Tutup">×</button></div>
                <input class="field w-full" required name="name" value="{{ $audience->name }}" aria-label="Nama audiens">
                <input class="field w-full" name="icon" value="{{ $audience->icon }}" placeholder="Ikon">
                <textarea class="field w-full" name="description" rows="3" placeholder="Deskripsi">{{ $audience->description }}</textarea>
                <button class="brand-button-dark">Simpan perubahan</button>
            </form></dialog>
        @empty
            <p class="text-sm text-slate-500">Buat kelompok pengguna pertama.</p>
        @endforelse
    </div>
    <dialog id="audience-form" class="modal"><form method="POST" action="{{ route('audiences.store') }}" class="space-y-3">@csrf
        <div class="flex items-center justify-between"><h2 class="text-xl font-black">Audiens baru</h2><button type="button" class="modal-close" onclick="this.closest('dialog').close()" aria-label="Tutup">×</button></div>
        <input class="field w-full" required name="name" placeholder="Contoh: Pacaran">
        <input class="field w-full" name="icon" placeholder="Ikon">
        <textarea class="field w-full" name="description" placeholder="Deskripsi"></textarea>
        <button class="brand-button-dark">Buat audiens</button>
    </form></dialog>
</x-workspace>
