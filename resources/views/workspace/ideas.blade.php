<x-workspace heading="Kotak Ide">
    @php($statusLabels = ['New' => 'Baru', 'Discuss' => 'Diskusi', 'Research' => 'Riset', 'Approved' => 'Disetujui', 'Rejected' => 'Ditolak', 'Planned' => 'Direncanakan'])
    <div class="mb-6 flex justify-end"><button type="button" onclick="document.getElementById('idea-form').showModal()" class="brand-button inline-flex items-center gap-2"><i data-lucide="plus" class="h-4 w-4"></i> Tambah ide</button></div>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($ideas as $idea)
            <article class="card entity-card idea-card">
                <div class="flex items-start justify-between gap-2"><span class="badge">{{ $statusLabels[$idea->status] ?? $idea->status }}</span><span class="truncate text-xs text-slate-500">{{ $idea->audience?->name ?? 'Tanpa audiens' }}</span></div>
                <h2 class="mt-4 text-base font-bold text-slate-800">{{ $idea->title }}</h2>
                <p class="mt-2 line-clamp-3 min-h-14 text-sm leading-5 text-slate-600">{{ $idea->description ?: 'Belum ada deskripsi.' }}</p>
                <p class="mb-4 mt-3 text-xs text-slate-500">oleh {{ $idea->submitter?->name ?? 'Anggota' }}</p>
                <div class="card-actions">
                    <button type="button" onclick="document.getElementById('idea-{{ $idea->id }}').showModal()" class="action-button"><i data-lucide="pencil"></i> Edit</button>
                    @if($idea->status !== 'Planned')<form method="POST" action="{{ route('ideas.convert', $idea) }}" data-confirm-title="Jadikan ide ini produk?" data-confirm-text="Produk baru akan dibuat dari ide ini.">@csrf<button class="action-button"><i data-lucide="arrow-right"></i> Jadikan produk</button></form>@endif
                    @if(auth()->user()->role === 'admin')<form method="POST" action="{{ route('ideas.destroy', $idea) }}" data-confirm-title="Hapus ide ini?" data-confirm-text="Ide akan dihapus permanen." data-confirm-kind="danger">@csrf @method('DELETE')<button class="action-button action-danger"><i data-lucide="trash-2"></i> Hapus</button></form>@endif
                </div>
            </article>
            <dialog id="idea-{{ $idea->id }}" class="modal"><form method="POST" action="{{ route('ideas.update', $idea) }}" class="space-y-3" data-confirm-title="Simpan perubahan ide?">@csrf @method('PUT')
                <div class="flex items-center justify-between"><h2 class="text-xl font-black">Edit ide</h2><button type="button" class="modal-close" onclick="this.closest('dialog').close()" aria-label="Tutup">×</button></div>
                <input class="field w-full" required name="title" value="{{ $idea->title }}" aria-label="Judul ide">
                <textarea class="field w-full" name="description" rows="2" placeholder="Deskripsi">{{ $idea->description }}</textarea>
                <textarea class="field w-full" name="problem" rows="2" placeholder="Masalah">{{ $idea->problem }}</textarea>
                <textarea class="field w-full" name="proposed_solution" rows="2" placeholder="Solusi yang diusulkan">{{ $idea->proposed_solution }}</textarea>
                <input class="field w-full" name="monetization_idea" value="{{ $idea->monetization_idea }}" placeholder="Ide monetisasi">
                <div class="grid grid-cols-2 gap-3"><select class="field" name="audience_id" aria-label="Audiens"><option value="">Audiens</option>@foreach($audiences as $audience)<option value="{{ $audience->id }}" @selected($idea->audience_id === $audience->id)>{{ $audience->name }}</option>@endforeach</select><select class="field" name="status" aria-label="Status">@foreach($statusLabels as $value => $label)<option value="{{ $value }}" @selected($idea->status === $value)>{{ $label }}</option>@endforeach</select></div>
                <button class="brand-button-dark">Simpan perubahan</button>
            </form></dialog>
        @empty
            <p class="text-sm text-slate-500">Punya ide produk baru? Tambahkan sekarang.</p>
        @endforelse
    </div>
    <dialog id="idea-form" class="modal"><form method="POST" action="{{ route('ideas.store') }}" class="space-y-3">@csrf
        <div class="flex items-center justify-between"><h2 class="text-xl font-black">Ide baru</h2><button type="button" class="modal-close" onclick="this.closest('dialog').close()" aria-label="Tutup">×</button></div>
        <input class="field w-full" required name="title" placeholder="Judul ide">
        <textarea class="field w-full" name="description" placeholder="Deskripsi"></textarea>
        <textarea class="field w-full" name="problem" placeholder="Masalah"></textarea>
        <textarea class="field w-full" name="proposed_solution" placeholder="Solusi yang diusulkan"></textarea>
        <input class="field w-full" name="monetization_idea" placeholder="Ide monetisasi">
        <select class="field w-full" name="audience_id" aria-label="Audiens"><option value="">Audiens</option>@foreach($audiences as $audience)<option value="{{ $audience->id }}">{{ $audience->name }}</option>@endforeach</select>
        <button class="brand-button-dark">Kirim ide</button>
    </form></dialog>
</x-workspace>
