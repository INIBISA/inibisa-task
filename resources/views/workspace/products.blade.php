<x-workspace heading="Products">
    @php($priorities = ['urgent' => 'Mendesak', 'high' => 'Tinggi', 'normal' => 'Normal', 'low' => 'Rendah', 'P0' => 'Mendesak', 'P1' => 'Tinggi', 'P2' => 'Normal', 'P3' => 'Rendah'])
    <div class="mb-6 flex justify-end"><button onclick="document.getElementById('product-form').showModal()"
            class="rounded-xl bg-lime-300 px-4 py-2 text-sm font-bold">+ Produk baru</button></div>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($products as $product)
            @php($priorityClass = ['P0' => 'urgent', 'P1' => 'high', 'P2' => 'normal', 'P3' => 'low'][$product->priority] ?? $product->priority)
            <article class="card">
                <div class="flex justify-between"><span class="badge">{{ $product->stage }}</span><span
                        class="badge priority-{{ $priorityClass }}">{{ $priorities[$product->priority] ?? $product->priority }}</span>
                </div>
                <h2 class="mt-4 text-lg font-black">{{ $product->name }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $product->audience?->name ?? 'Tanpa audience' }} ·
                    {{ $product->owner?->name ?? 'Tanpa owner' }}</p>
                <p class="mt-4 text-sm text-slate-600">{{ $product->description ?: 'Belum ada deskripsi.' }}</p>
                @php($total = $product->tasks->count()) @php($done = $product->tasks->where('status', 'Done')->count())<div class="mt-5">
                    <div class="mb-1 flex justify-between text-xs">
                        <span>Progress</span><span>{{ $total ? round(($done / $total) * 100) : 0 }}%</span></div>
                    <div class="h-2 overflow-hidden rounded bg-slate-100">
                        <div class="h-full bg-lime-400" style="width:{{ $total ? round(($done / $total) * 100) : 0 }}%"></div>
                    </div>
                </div>
        </article>@empty<div class="card text-slate-500">Belum ada produk.</div>
        @endforelse
    </div>
    <dialog id="product-form" class="modal">
        <form method="POST" action="{{ route('products.store') }}" class="space-y-3">@csrf<h2
                class="text-xl font-black">Produk baru</h2><input class="field w-full" required name="name"
                placeholder="Nama produk">
            <textarea class="field w-full" name="description" placeholder="Deskripsi"></textarea>
            <textarea class="field w-full" name="problem" placeholder="Problem"></textarea>
            <textarea class="field w-full" name="solution" placeholder="Solution"></textarea>
            <div class="grid grid-cols-2 gap-2"><select class="field" name="audience_id">
                    <option value="">Audience</option>
                    @foreach ($audiences as $a)
                        <option value="{{ $a->id }}">{{ $a->name }}</option>
                    @endforeach
                </select>
                <select class="field" name="owner_id">
                    <option value="">Owner</option>
                    @foreach ($members as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                    @endforeach
                </select>
                <select class="field" name="stage">
                    @foreach (['Idea', 'Validation', 'Design', 'Development', 'Content Preparation', 'Ready To Launch', 'Launched', 'Growth'] as $s)
                        <option>{{ $s }}</option>
                    @endforeach
                </select>
                <select class="field" name="priority">
                    @foreach (['urgent' => 'Mendesak', 'high' => 'Tinggi', 'normal' => 'Normal', 'low' => 'Rendah'] as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div><input class="field w-full" type="date" name="target_launch"><button
                class="rounded-xl bg-slate-900 px-4 py-2 text-white">Buat</button>
        </form>
    </dialog>
</x-workspace>
