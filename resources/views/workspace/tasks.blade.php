<x-workspace heading="Task Board">
    @php($priorities = ['urgent' => 'Mendesak', 'high' => 'Tinggi', 'normal' => 'Normal', 'low' => 'Rendah', 'P0' => 'Mendesak', 'P1' => 'Tinggi', 'P2' => 'Normal', 'P3' => 'Rendah'])

    <div class="mb-6 flex flex-wrap gap-3">
        <form class="flex flex-wrap gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Cari task" class="field">
            <select name="status" class="field">
                <option value="">Semua status</option>
                @foreach (['Backlog', 'Planned', 'In Progress', 'Review', 'Done'] as $status)
                    <option @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-white px-4 text-sm font-bold ring-1 ring-slate-200">Filter</button>
        </form>
        <button onclick="document.getElementById('task-form').showModal()"
            class="rounded-xl bg-lime-300 px-4 py-2 text-sm font-bold">+ Buat task</button>
    </div>
    <p class="mb-4 text-sm text-slate-500">Tarik pegangan <b>⠿</b> pada kartu ke kolom status lain.</p>

    <div class="overflow-x-auto pb-4">
        <div class="grid min-w-[1100px] grid-cols-5 gap-4">
            @foreach (['Backlog', 'Planned', 'In Progress', 'Review', 'Done'] as $status)
                <section class="rounded-2xl bg-slate-100 p-3">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="text-sm font-black">{{ strtoupper($status) }}</h2><span
                            class="text-xs text-slate-400">{{ $tasks->where('status', $status)->count() }}</span>
                    </div>
                    <div class="min-h-32 space-y-3 rounded-xl" data-dropzone="{{ $status }}">
                        @forelse($tasks->where('status', $status) as $task)
                            <article data-task-id="{{ $task->id }}" class="card relative">
                                <button draggable="true" type="button"
                                    class="drag-handle absolute right-3 top-3 cursor-grab rounded p-1 text-slate-300 hover:text-slate-700 active:cursor-grabbing"
                                    aria-label="Tarik task">⠿</button>
                                <button type="button"
                                    onclick="document.getElementById('task-{{ $task->id }}').showModal()"
                                    class="block w-full text-left">
                                    @php($priorityClass = ['P0' => 'urgent', 'P1' => 'high', 'P2' => 'normal', 'P3' => 'low'][$task->priority] ?? $task->priority)
                                    <div class="mb-2 flex justify-between pr-6"><span
                                            class="badge priority-{{ $priorityClass }}">{{ $priorities[$task->priority] ?? $task->priority }}</span>
                                        @if ($task->is_blocked)
                                            <span class="text-xs font-bold text-rose-600">Blocked</span>
                                        @endif
                                    </div>
                                    <p class="font-bold">{{ $task->title }}</p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ $task->product?->name ?? 'Tanpa produk' }}</p>
                                    <div class="mt-3 flex justify-between text-xs text-slate-500">
                                        <span>{{ $task->assignees->pluck('name')->join(', ') ?: 'Unassigned' }}</span><span>{{ $task->due_date?->format('d M') ?? 'No date' }}</span>
                                    </div>
                                </button>
                            </article>
                            <dialog id="task-{{ $task->id }}" class="modal">
                                <div class="space-y-5">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <div class="mb-2 flex gap-2"><span
                                                    class="badge">{{ $task->status }}</span><span
                                                    class="badge priority-{{ $priorityClass }}">{{ $priorities[$task->priority] ?? $task->priority }}</span>
                                            </div>
                                            <h2 class="text-xl font-black">{{ $task->title }}</h2>
                                        </div><button onclick="this.closest('dialog').close()"
                                            class="text-xl text-slate-400">×</button>
                                    </div>
                                    <p class="text-sm text-slate-600">
                                        {{ $task->description ?: 'Belum ada deskripsi.' }}</p>
                                    <div class="grid grid-cols-2 gap-4 border-y border-slate-100 py-4 text-sm">
                                        <div>
                                            <p class="label">Produk</p>
                                            <p class="font-semibold">{{ $task->product?->name ?? 'Tanpa produk' }}</p>
                                        </div>
                                        <div>
                                            <p class="label">Deadline</p>
                                            <p class="font-semibold">
                                                {{ $task->due_date?->format('d M Y') ?? 'Belum ada' }}</p>
                                        </div>
                                        <div>
                                            <p class="label">Assignee</p>
                                            <p class="font-semibold">
                                                {{ $task->assignees->pluck('name')->join(', ') ?: 'Belum ditugaskan' }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="label">Status</p>
                                            <p class="font-semibold">
                                                {{ $task->is_blocked ? 'Blocked: ' . $task->blocked_reason : $task->status }}
                                            </p>
                                        </div>
                                    </div>
                                    <details>
                                        <summary class="cursor-pointer text-sm font-bold text-lime-700">Edit task
                                        </summary>
                                        <form class="mt-3 space-y-2" method="POST"
                                            action="{{ route('tasks.update', $task) }}">@csrf @method('PUT')<input
                                                class="field w-full" name="title" value="{{ $task->title }}">
                                            <textarea class="field w-full" name="description">{{ $task->description }}</textarea>
                                            <div class="grid grid-cols-2 gap-2"><select class="field" name="status">
                                                    @foreach (['Backlog', 'Planned', 'In Progress', 'Review', 'Done'] as $option)
                                                        <option @selected($task->status === $option)>{{ $option }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <select class="field" name="priority">
                                                    @foreach ($priorities as $value => $label)
                                                        <option value="{{ $value }}"
                                                            @selected($task->priority === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div><button
                                                class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white">Simpan
                                                perubahan</button>
                                        </form>
                                    </details>
                                </div>
                            </dialog>
                        @empty <p class="py-6 text-center text-xs text-slate-400">Drop task di sini</p>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    </div>

    <dialog id="task-form" class="modal">
        <form method="POST" action="{{ route('tasks.store') }}" class="space-y-3">@csrf<h2 class="text-xl font-black">
                Task baru</h2><input class="field w-full" required name="title"
                placeholder="Apa yang perlu dikerjakan?">
            <div class="grid grid-cols-2 gap-2"><select class="field" name="product_id">
                    <option value="">Tanpa produk</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
                <select class="field" name="priority">
                    @foreach ($priorities as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select class="field" name="status">
                    @foreach (['Backlog', 'Planned', 'In Progress', 'Review', 'Done'] as $status)
                        <option>{{ $status }}</option>
                    @endforeach
                </select>
            </div><button class="rounded-xl bg-slate-900 px-4 py-2 text-white">Buat</button>
        </form>
    </dialog>

    <script>
        const token = document.querySelector('meta[name="csrf-token"]').content;
        let dragged;
        document.querySelectorAll('.drag-handle').forEach(handle => {
            handle.addEventListener('dragstart', () => {
                dragged = handle.closest('[data-task-id]');
                dragged.classList.add('opacity-40');
            });
            handle.addEventListener('dragend', () => dragged?.classList.remove('opacity-40'));
        });
        document.querySelectorAll('[data-dropzone]').forEach(zone => {
            zone.addEventListener('dragover', event => {
                event.preventDefault();
                zone.classList.add('ring-2', 'ring-lime-400');
            });
            zone.addEventListener('dragleave', () => zone.classList.remove('ring-2', 'ring-lime-400'));
            zone.addEventListener('drop', async event => {
                event.preventDefault();
                zone.classList.remove('ring-2', 'ring-lime-400');
                if (!dragged) return;
                zone.append(dragged);
                await fetch(`/tasks/${dragged.dataset.taskId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        status: zone.dataset.dropzone
                    })
                });
                location.reload();
            });
        });
    </script>
</x-workspace>
