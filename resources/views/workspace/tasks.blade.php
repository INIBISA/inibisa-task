<x-workspace heading="Papan Tugas">
    @php($priorities = ['urgent' => 'Mendesak', 'high' => 'Tinggi', 'normal' => 'Normal', 'low' => 'Rendah', 'P0' => 'Mendesak', 'P1' => 'Tinggi', 'P2' => 'Normal', 'P3' => 'Rendah'])
    @php($priorityClass = fn ($priority) => ['P0' => 'urgent', 'P1' => 'high', 'P2' => 'normal', 'P3' => 'low'][$priority] ?? $priority)
    @php($statusLabels = ['Backlog' => 'Menunggu', 'Planned' => 'Direncanakan', 'In Progress' => 'Dikerjakan', 'Review' => 'Ditinjau', 'Done' => 'Selesai'])
    @php($mobileInitialStatus = isset($statusLabels[request('status')]) ? request('status') : ($tasks->firstWhere('status', 'In Progress') ? 'In Progress' : ($tasks->first()?->status ?? 'Backlog')))

    <div class="mb-5 flex flex-col gap-3 sm:mb-6 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
        <form class="grid grid-cols-[minmax(0,1fr)_auto] gap-2 sm:flex sm:flex-wrap">
            @if(request()->filled('product'))<input type="hidden" name="product" value="{{ request('product') }}">@endif
            <input name="q" value="{{ request('q') }}" placeholder="Cari tugas" class="field col-span-2 min-w-0 sm:col-span-1 sm:min-w-48" aria-label="Cari tugas">
            <select name="status" class="field min-w-0" aria-label="Filter status"><option value="">Semua status</option>@foreach($statusLabels as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select>
            <button class="action-button h-10 px-3"><i data-lucide="funnel"></i> Filter</button>
        </form>
        <button type="button" onclick="document.getElementById('task-form').showModal()" class="brand-button inline-flex min-h-11 items-center justify-center gap-2"><i data-lucide="plus" class="h-4 w-4"></i> Buat tugas</button>
    </div>

    <nav class="mb-6 flex max-w-full gap-1.5 overflow-x-auto rounded-lg border border-slate-200 bg-slate-50/80 p-1.5" aria-label="Filter produk">
        <a href="{{ route('tasks', request()->except('product')) }}" class="task-tab {{ !request()->filled('product') ? 'task-tab-active' : '' }}" @if(!request()->filled('product')) aria-current="page" @endif><i data-lucide="layers-3" class="h-4 w-4"></i> Semua tugas</a>
        @foreach($products as $product)
            <a href="{{ route('tasks', array_merge(request()->except('product'), ['product' => $product->id])) }}" class="task-tab {{ (string) request('product') === (string) $product->id ? 'task-tab-active' : '' }}" @if((string) request('product') === (string) $product->id) aria-current="page" @endif><i data-lucide="package" class="h-4 w-4"></i><span class="max-w-40 truncate">{{ $product->name }}</span></a>
        @endforeach
        <a href="{{ route('tasks', array_merge(request()->except('product'), ['product' => 'none'])) }}" class="task-tab {{ request('product') === 'none' ? 'task-tab-active' : '' }}" @if(request('product') === 'none') aria-current="page" @endif><i data-lucide="inbox" class="h-4 w-4"></i> Tanpa produk</a>
    </nav>

    <div class="mobile-status-tabs mb-3 flex gap-2 overflow-x-auto pb-1 lg:hidden" role="tablist" aria-label="Status tugas">
        @foreach($statusLabels as $status => $label)
            <button type="button" id="task-tab-{{ $loop->index }}" class="mobile-status-tab" data-status-tab="{{ $status }}" role="tab" aria-controls="task-panel-{{ $loop->index }}" aria-selected="false">{{ $label }} <span>{{ $tasks->where('status', $status)->count() }}</span></button>
        @endforeach
    </div>
    <div class="task-board pb-5 lg:overflow-x-auto" data-initial-status="{{ $mobileInitialStatus }}"><div class="task-board-grid grid gap-4 lg:min-w-[1160px] lg:grid-cols-5">
        @foreach($statusLabels as $status => $label)
            <section id="task-panel-{{ $loop->index }}" class="task-status-column rounded-2xl border border-slate-200 bg-slate-50/70 p-3" data-mobile-status="{{ $status }}" role="tabpanel" aria-labelledby="task-tab-{{ $loop->index }}">
                <div class="mb-3 flex items-center justify-between px-1"><h2 class="text-xs font-bold text-slate-700">{{ $label }}</h2><span class="task-tab-count">{{ $tasks->where('status', $status)->count() }}</span></div>
                <div class="task-dropzone min-h-40 space-y-3" data-dropzone="{{ $status }}">
                    @forelse($tasks->where('status', $status) as $task)
                        @php($class = $priorityClass($task->priority))
                        <article data-task-id="{{ $task->id }}" data-update-url="{{ route('tasks.update', $task) }}" data-dialog-target="task-{{ $task->id }}" class="task-card card relative p-4 transition hover:shadow-md" tabindex="0" role="button" aria-label="Buka tugas {{ $task->title }}">
                            <button type="button" class="drag-handle absolute right-3 top-3 hidden cursor-grab rounded-md px-1.5 py-1 text-slate-400 hover:bg-slate-100 active:cursor-grabbing lg:block" aria-label="Pindahkan tugas">⠿</button>
                            <div class="block w-full text-left">
                                <div class="mb-3 flex items-center gap-2 pr-7"><span class="badge priority-{{ $class }}" data-priority="{{ $task->priority }}">{{ $priorities[$task->priority] ?? $task->priority }}</span>@if($task->is_blocked)<span class="text-xs font-bold text-rose-600">Terblokir</span>@endif</div>
                                <p class="pr-2 text-sm font-bold leading-5 text-slate-800">{{ $task->title }}</p>
                                <p class="mt-1.5 truncate text-xs text-slate-500">{{ $task->product?->name ?? 'Tanpa produk' }}</p>
                                <div class="mt-4 flex items-center justify-between gap-2 border-t border-slate-100 pt-3 text-xs text-slate-500"><span class="truncate">{{ $task->assignees->pluck('name')->join(', ') ?: 'Belum ditugaskan' }}</span><span class="shrink-0">{{ $task->due_date?->format('d M') ?? 'Tanpa tanggal' }}</span></div>
                            </div>
                        </article>
                        <dialog id="task-{{ $task->id }}" class="modal"><div class="space-y-5">
                            <div class="flex items-start justify-between gap-4"><div><div class="mb-2 flex gap-2"><span class="badge">{{ $statusLabels[$task->status] ?? $task->status }}</span><span class="badge priority-{{ $class }}" data-priority="{{ $task->priority }}">{{ $priorities[$task->priority] ?? $task->priority }}</span></div><h2 class="text-xl font-black leading-tight">{{ $task->title }}</h2></div><button type="button" onclick="this.closest('dialog').close()" class="modal-close" aria-label="Tutup">×</button></div>
                            <p class="text-sm leading-6 text-slate-600">{{ $task->description ?: 'Belum ada deskripsi.' }}</p>
                            @if(auth()->user()->role === 'admin' || $task->created_by === auth()->id())
                                <form method="POST" action="{{ route('tasks.update', $task) }}" class="flex items-end gap-2">@csrf @method('PUT')
                                    <label class="min-w-0 flex-1"><span class="label block">Pindahkan ke</span><select class="field min-h-11 w-full" name="status">@foreach($statusLabels as $value => $option)<option value="{{ $value }}" @selected($task->status === $value)>{{ $option }}</option>@endforeach</select></label>
                                    <button type="submit" class="brand-button min-h-11">Simpan</button>
                                </form>
                            @endif
                            <div class="grid grid-cols-2 gap-x-6 gap-y-4 border-y border-slate-100 py-4 text-sm"><div><p class="label">Produk</p><p class="font-semibold">{{ $task->product?->name ?? 'Tanpa produk' }}</p></div><div><p class="label">Tenggat</p><p class="font-semibold">{{ $task->due_date?->format('d M Y') ?? 'Belum ada' }}</p></div><div><p class="label">Ditugaskan ke</p><p class="font-semibold">{{ $task->assignees->pluck('name')->join(', ') ?: 'Belum ditugaskan' }}</p></div><div><p class="label">Status</p><p class="font-semibold">{{ $statusLabels[$task->status] ?? $task->status }}</p></div></div>
                            @if(auth()->user()->role === 'admin' || $task->created_by === auth()->id())
                                <details><summary class="action-button cursor-pointer list-none"><i data-lucide="pencil"></i> Edit tugas</summary>
                                    <form method="POST" action="{{ route('tasks.update', $task) }}" class="mt-4 space-y-3" data-confirm-title="Simpan perubahan tugas?">@csrf @method('PUT')
                                        <input class="field w-full" required name="title" value="{{ $task->title }}" aria-label="Judul tugas">
                                        <textarea class="field w-full" name="description" rows="3" placeholder="Deskripsi">{{ $task->description }}</textarea>
                                        <div class="grid grid-cols-2 gap-3"><select class="field" name="product_id" aria-label="Produk"><option value="">Tanpa produk</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected($task->product_id === $product->id)>{{ $product->name }}</option>@endforeach</select><input class="field" type="date" name="due_date" value="{{ $task->due_date?->format('Y-m-d') }}" aria-label="Tenggat"></div>
                                        <div class="grid grid-cols-2 gap-3"><select class="field" name="status" aria-label="Status">@foreach($statusLabels as $value => $option)<option value="{{ $value }}" @selected($task->status === $value)>{{ $option }}</option>@endforeach</select><select class="field" name="priority" aria-label="Prioritas">@foreach(['urgent' => 'Mendesak', 'high' => 'Tinggi', 'normal' => 'Normal', 'low' => 'Rendah'] as $value => $option)<option value="{{ $value }}" @selected($class === $value)>{{ $option }}</option>@endforeach</select></div>
                                        <p class="label">Tugaskan ke</p><input type="hidden" name="assignees_present" value="1"><x-assignee-picker :members="$members" :selected="$task->assignees->pluck('id')" />
                                        <button class="brand-button-dark">Simpan perubahan</button>
                                    </form>
                                </details>
                            @endif
                            @if(auth()->user()->role === 'admin')<form method="POST" action="{{ route('tasks.destroy', $task) }}" data-confirm-title="Hapus tugas ini?" data-confirm-text="Tugas dan sub-tugasnya akan dihapus permanen." data-confirm-kind="danger">@csrf @method('DELETE')<button class="action-button action-danger"><i data-lucide="trash-2"></i> Hapus tugas</button></form>@endif
                        </div></dialog>
                    @empty
                        <p class="py-8 text-center text-xs text-slate-400">Belum ada tugas</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div></div>

    <dialog id="task-form" class="modal"><form method="POST" action="{{ route('tasks.store') }}" class="space-y-4">@csrf
        <div class="flex items-center justify-between"><h2 class="text-xl font-black">Tugas baru</h2><button type="button" onclick="this.closest('dialog').close()" class="modal-close" aria-label="Tutup">×</button></div>
        <input class="field w-full" required name="title" placeholder="Apa yang perlu dikerjakan?">
        <textarea class="field w-full" name="description" rows="2" placeholder="Deskripsi (opsional)"></textarea>
        <div class="grid grid-cols-2 gap-3"><select class="field" name="product_id" aria-label="Produk"><option value="">Tanpa produk</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected((string) request('product') === (string) $product->id)>{{ $product->name }}</option>@endforeach</select><input class="field" type="date" name="due_date" aria-label="Tenggat"><select class="field" name="priority" aria-label="Prioritas">@foreach(['normal' => 'Normal', 'urgent' => 'Mendesak', 'high' => 'Tinggi', 'low' => 'Rendah'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select><select class="field" name="status" aria-label="Status">@foreach($statusLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
        <p class="label">Tugaskan ke</p><x-assignee-picker :members="$members" />
        <button class="brand-button-dark">Buat tugas</button>
    </form></dialog>
</x-workspace>
