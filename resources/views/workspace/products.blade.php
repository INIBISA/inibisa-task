<x-workspace heading="Produk">
    @php($priorities = ['urgent' => 'Mendesak', 'high' => 'Tinggi', 'normal' => 'Normal', 'low' => 'Rendah', 'P0' => 'Mendesak', 'P1' => 'Tinggi', 'P2' => 'Normal', 'P3' => 'Rendah'])
    @php($stageLabels = ['Idea' => 'Ide', 'Validation' => 'Validasi', 'Design' => 'Desain', 'Development' => 'Pengembangan', 'Content Preparation' => 'Persiapan konten', 'Ready To Launch' => 'Siap diluncurkan', 'Launched' => 'Diluncurkan', 'Growth' => 'Pertumbuhan'])
    <div class="mb-6 flex justify-end"><button type="button" onclick="document.getElementById('product-form').showModal()" class="brand-button inline-flex items-center gap-2"><i data-lucide="plus" class="h-4 w-4"></i> Produk baru</button></div>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($products as $product)
            @php($priorityClass = ['P0' => 'urgent', 'P1' => 'high', 'P2' => 'normal', 'P3' => 'low'][$product->priority] ?? $product->priority)
            @php($total = $product->tasks->count())
            @php($progress = $total ? round($product->tasks->where('status', 'Done')->count() / $total * 100) : 0)
            <article class="card entity-card product-card {{ $product->status === 'archived' ? 'opacity-70' : '' }}">
                <div class="flex items-start justify-between gap-2"><span class="badge">{{ $stageLabels[$product->stage] ?? $product->stage }}</span><span class="badge priority-{{ $priorityClass }}" data-priority="{{ $product->priority }}">{{ $priorities[$product->priority] ?? $product->priority }}</span></div>
                <h2 class="mt-4 text-base font-bold text-slate-800">{{ $product->name }}</h2>
                <p class="mt-1 text-xs text-slate-500">{{ $product->audience?->name ?? 'Tanpa audiens' }} · {{ $product->owner?->name ?? 'Tanpa penanggung jawab' }}</p>
                <p class="my-4 line-clamp-2 min-h-10 text-sm leading-5 text-slate-600">{{ $product->description ?: 'Belum ada deskripsi.' }}</p>
                <div class="mb-4"><div class="mb-1.5 flex justify-between text-xs font-medium text-slate-500"><span>Progres</span><span>{{ $progress }}%</span></div><div class="h-1.5 overflow-hidden rounded bg-slate-100"><div class="h-full rounded bg-blue-500" style="width: {{ $progress }}%"></div></div></div>
                <div class="card-actions">
                    <button type="button" onclick="document.getElementById('product-{{ $product->id }}').showModal()" class="action-button"><i data-lucide="pencil"></i> Edit</button>
                    @if($product->status !== 'archived')<form method="POST" action="{{ route('products.archive', $product) }}" data-confirm-title="Arsipkan produk ini?" data-confirm-text="Produk tetap tersimpan dan dapat dilihat di daftar." data-confirm-kind="danger">@csrf @method('PATCH')<button class="action-button"><i data-lucide="archive"></i> Arsipkan</button></form>@endif
                    @if(auth()->user()->role === 'admin')<form method="POST" action="{{ route('products.destroy', $product) }}" data-confirm-title="Hapus produk ini?" data-confirm-text="Produk dihapus permanen. Tugas yang terkait tetap ada tanpa produk." data-confirm-kind="danger">@csrf @method('DELETE')<button class="action-button action-danger"><i data-lucide="trash-2"></i> Hapus</button></form>@endif
                </div>
            </article>
            <dialog id="product-{{ $product->id }}" class="modal"><form method="POST" action="{{ route('products.update', $product) }}" class="space-y-3" data-confirm-title="Simpan perubahan produk?">@csrf @method('PUT')
                <div class="flex items-center justify-between"><h2 class="text-xl font-black">Edit produk</h2><button type="button" class="modal-close" onclick="this.closest('dialog').close()" aria-label="Tutup">×</button></div>
                <x-product-fields :product="$product" :audiences="$audiences" :members="$members" :stage-labels="$stageLabels" />
                <button class="brand-button-dark">Simpan perubahan</button>
            </form></dialog>
        @empty
            <p class="text-sm text-slate-500">Belum ada produk.</p>
        @endforelse
    </div>
    <dialog id="product-form" class="modal"><form method="POST" action="{{ route('products.store') }}" class="space-y-3">@csrf
        <div class="flex items-center justify-between"><h2 class="text-xl font-black">Produk baru</h2><button type="button" class="modal-close" onclick="this.closest('dialog').close()" aria-label="Tutup">×</button></div>
        <x-product-fields :audiences="$audiences" :members="$members" :stage-labels="$stageLabels" />
        <button class="brand-button-dark">Buat produk</button>
    </form></dialog>
</x-workspace>
