<x-workspace heading="Task Board">
@php($priorities = ['urgent' => 'Mendesak', 'high' => 'Tinggi', 'normal' => 'Normal', 'low' => 'Rendah', 'P0' => 'Mendesak', 'P1' => 'Tinggi', 'P2' => 'Normal', 'P3' => 'Rendah'])
@php($priorityClass = fn ($priority) => ['P0'=>'urgent','P1'=>'high','P2'=>'normal','P3'=>'low'][$priority] ?? $priority)

<div class="mb-7 flex flex-wrap items-center gap-3">
    <form class="flex flex-wrap gap-2"><input name="q" value="{{ request('q') }}" placeholder="Cari task" class="field"><select name="status" class="field"><option value="">Semua status</option>@foreach(['Backlog','Planned','In Progress','Review','Done'] as $status)<option @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select><button class="rounded-xl bg-white px-4 text-sm font-bold ring-1 ring-slate-200">Filter</button></form>
    <button onclick="document.getElementById('task-form').showModal()" class="brand-button">+ Buat task</button>
</div>
<nav class="mb-5 flex max-w-full gap-2 overflow-x-auto border-b border-slate-200 pb-3" aria-label="Filter produk">
    <a href="{{ route('tasks', request()->except('product')) }}" class="task-tab {{ !request()->filled('product') ? 'task-tab-active' : '' }}">Semua Task</a>
    @foreach($products as $product)
        <a href="{{ route('tasks', array_merge(request()->except('product'), ['product' => $product->id])) }}" class="task-tab {{ (string) request('product') === (string) $product->id ? 'task-tab-active' : '' }}">{{ $product->name }}</a>
    @endforeach
    <a href="{{ route('tasks', array_merge(request()->except('product'), ['product' => 'none'])) }}" class="task-tab {{ request('product') === 'none' ? 'task-tab-active' : '' }}">Tanpa Produk</a>
</nav>
<p class="mb-5 text-sm text-slate-500">Tarik pegangan <b>⠿</b> pada kartu ke kolom status lain.</p>

<div class="overflow-x-auto pb-5"><div class="grid min-w-[1160px] grid-cols-5 gap-5">
@foreach(['Backlog','Planned','In Progress','Review','Done'] as $status)
    <section class="rounded-3xl bg-slate-100/80 p-4">
        <div class="mb-4 flex items-center justify-between px-1"><h2 class="text-xs font-black tracking-wide text-slate-600">{{ strtoupper($status) }}</h2><span class="rounded-full bg-white px-2 py-0.5 text-xs font-bold text-slate-400">{{ $tasks->where('status', $status)->count() }}</span></div>
        <div class="min-h-40 space-y-3 rounded-2xl" data-dropzone="{{ $status }}">
        @forelse($tasks->where('status', $status) as $task)
            @php($class = $priorityClass($task->priority))
            <article data-task-id="{{ $task->id }}" class="card relative p-4 transition hover:-translate-y-0.5 hover:shadow-md">
                <button draggable="true" type="button" class="drag-handle absolute right-3 top-3 cursor-grab rounded-lg px-1.5 py-1 text-slate-300 hover:bg-slate-100 hover:text-slate-700 active:cursor-grabbing" aria-label="Tarik task">⠿</button>
                <button type="button" onclick="document.getElementById('task-{{ $task->id }}').showModal()" class="block w-full text-left">
                    <div class="mb-3 flex items-center gap-2 pr-8"><span class="badge priority-{{ $class }}" data-priority="{{ $task->priority }}">{{ $priorities[$task->priority] ?? $task->priority }}</span>@if($task->is_blocked)<span class="text-xs font-bold text-rose-600">Blocked</span>@endif</div>
                    <p class="pr-3 text-sm font-bold leading-5 text-slate-800">{{ $task->title }}</p><p class="mt-1.5 text-xs text-slate-400">{{ $task->product?->name ?? 'Tanpa produk' }}</p>
                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs text-slate-500"><span class="max-w-[120px] truncate">{{ $task->assignees->pluck('name')->join(', ') ?: 'Unassigned' }}</span><span>{{ $task->due_date?->format('d M') ?? 'No date' }}</span></div>
                </button>
            </article>
            <dialog id="task-{{ $task->id }}" class="modal"><div class="space-y-6"><div class="flex items-start justify-between gap-4"><div><div class="mb-3 flex gap-2"><span class="badge">{{ $task->status }}</span><span class="badge priority-{{ $class }}" data-priority="{{ $task->priority }}">{{ $priorities[$task->priority] ?? $task->priority }}</span></div><h2 class="text-xl font-black leading-tight">{{ $task->title }}</h2></div><button onclick="this.closest('dialog').close()" class="grid h-8 w-8 place-items-center rounded-lg text-xl text-slate-400 hover:bg-slate-100">×</button></div><p class="text-sm leading-6 text-slate-600">{{ $task->description ?: 'Belum ada deskripsi.' }}</p><div class="grid grid-cols-2 gap-x-6 gap-y-5 border-y border-slate-100 py-5 text-sm"><div><p class="label">Produk</p><p class="font-semibold">{{ $task->product?->name ?? 'Tanpa produk' }}</p></div><div><p class="label">Deadline</p><p class="font-semibold">{{ $task->due_date?->format('d M Y') ?? 'Belum ada' }}</p></div><div><p class="label">Assignee</p><p class="font-semibold">{{ $task->assignees->pluck('name')->join(', ') ?: 'Belum ditugaskan' }}</p></div><div><p class="label">Status</p><p class="font-semibold">{{ $task->is_blocked ? 'Blocked: '.$task->blocked_reason : $task->status }}</p></div></div><details><summary class="brand-link cursor-pointer text-sm">Edit task</summary><form class="mt-4 space-y-3" method="POST" action="{{ route('tasks.update', $task) }}">@csrf @method('PUT')<input class="field w-full" name="title" value="{{ $task->title }}"><textarea class="field w-full" name="description" rows="3">{{ $task->description }}</textarea><div class="grid grid-cols-2 gap-3"><select class="field" name="status">@foreach(['Backlog','Planned','In Progress','Review','Done'] as $option)<option @selected($task->status === $option)>{{ $option }}</option>@endforeach</select><select class="field" name="priority">@foreach(['urgent'=>'Mendesak','high'=>'Tinggi','normal'=>'Normal','low'=>'Rendah'] as $value => $label)<option value="{{ $value }}" @selected($class === $value)>{{ $label }}</option>@endforeach</select></div><button class="brand-button-dark">Simpan perubahan</button></form></details></div></dialog>
        @empty <p class="py-8 text-center text-xs text-slate-400">Drop task di sini</p> @endforelse
        </div>
    </section>
@endforeach
</div></div>

<dialog id="task-form" class="modal"><form method="POST" action="{{ route('tasks.store') }}" class="space-y-4">@csrf<h2 class="text-xl font-black">Task baru</h2><input class="field w-full" required name="title" placeholder="Apa yang perlu dikerjakan?"><div class="grid grid-cols-2 gap-3"><select class="field" name="product_id"><option value="">Tanpa produk</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }}</option>@endforeach</select><select class="field" name="priority">@foreach(['urgent'=>'Mendesak','high'=>'Tinggi','normal'=>'Normal','low'=>'Rendah'] as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select><select class="field" name="status">@foreach(['Backlog','Planned','In Progress','Review','Done'] as $status)<option>{{ $status }}</option>@endforeach</select></div><button class="brand-button-dark">Buat</button></form></dialog>

<script>
const token = document.querySelector('meta[name="csrf-token"]').content; let dragged;
document.querySelectorAll('.drag-handle').forEach(handle => { handle.addEventListener('dragstart', () => { dragged = handle.closest('[data-task-id]'); dragged.classList.add('opacity-40'); }); handle.addEventListener('dragend', () => dragged?.classList.remove('opacity-40')); });
document.querySelectorAll('[data-dropzone]').forEach(zone => { zone.addEventListener('dragover', event => { event.preventDefault(); zone.classList.add('ring-2', 'ring-cyan-300'); }); zone.addEventListener('dragleave', () => zone.classList.remove('ring-2', 'ring-cyan-300')); zone.addEventListener('drop', async event => { event.preventDefault(); zone.classList.remove('ring-2', 'ring-cyan-300'); if (!dragged) return; zone.append(dragged); await fetch(`/tasks/${dragged.dataset.taskId}`, {method:'PUT', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':token}, body:JSON.stringify({status:zone.dataset.dropzone})}); location.reload(); }); });
</script>
</x-workspace>
