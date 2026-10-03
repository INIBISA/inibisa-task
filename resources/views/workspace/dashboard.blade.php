<x-workspace heading="Overview">
    @php($priorities = ['urgent' => 'Mendesak', 'high' => 'Tinggi', 'normal' => 'Normal', 'low' => 'Rendah', 'P0' => 'Mendesak', 'P1' => 'Tinggi', 'P2' => 'Normal', 'P3' => 'Rendah'])
    <div class="mb-8 rounded-3xl bg-slate-900 p-7 text-white">
        <p class="text-xs font-bold tracking-widest text-lime-300">FOCUS THIS MONTH</p>
        <div class="mt-3 flex flex-wrap items-end justify-between gap-5">
            <div>
                <h2 class="text-3xl font-black">Build what matters.</h2>
                <p class="mt-2 text-slate-300">{{ $products->first()?->name ?? 'Mulai produk pertama Anda' }}</p>
            </div><a href="{{ route('products') }}"
                class="rounded-xl bg-lime-300 px-4 py-2 text-sm font-bold text-slate-950">Lihat produk</a>
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach (['Produk aktif' => $stats['active'], 'Sedang dikerjakan' => $stats['progress'], 'Selesai' => $stats['done'], 'Terblokir' => $stats['blocked'], 'Ideas' => $stats['ideas']] as $name => $value)
            <div class="card">
                <p class="text-sm text-slate-500">{{ $name }}</p>
                <p class="mt-2 text-3xl font-black">{{ $value }}</p>
            </div>
        @endforeach
    </div>
    <div class="mt-8 grid gap-7 xl:grid-cols-[1.5fr_1fr]">
        <section>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-bold">Task terbaru</h2><a class="text-sm font-semibold text-lime-700"
                    href="{{ route('tasks') }}">Buka board</a>
            </div>
            <div class="space-y-2">
                @forelse($tasks as $task)
                    @php($priorityClass = ['P0' => 'urgent', 'P1' => 'high', 'P2' => 'normal', 'P3' => 'low'][$task->priority] ?? $task->priority)
                    <div class="card flex items-center gap-3"><span
                            class="h-2.5 w-2.5 rounded-full {{ $task->is_blocked ? 'bg-rose-500' : 'bg-lime-500' }}"></span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold">{{ $task->title }}</p>
                            <p class="text-xs text-slate-400">{{ $task->product?->name ?? 'Tanpa produk' }} ·
                                {{ $task->status }}</p>
                        </div><span
                            class="badge priority-{{ $priorityClass }}">{{ $priorities[$task->priority] ?? $task->priority }}</span>
                </div>@empty<div class="card text-slate-500">Belum ada task. Buat pekerjaan pertama tim.</div>
                @endforelse
            </div>
        </section>
        <section>
            <h2 class="mb-3 font-bold">Aktivitas terbaru</h2>
            <div class="card space-y-4">
                @forelse($activities as $activity)
                    <div class="border-b border-slate-100 pb-3 last:border-0">
                        <p class="text-sm"><b>{{ $activity->user?->name ?? 'System' }}</b> {{ $activity->action }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ $activity->created_at->diffForHumans() }}</p>
                </div>@empty<p class="text-sm text-slate-500">Aktivitas akan tampil di sini.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-workspace>
