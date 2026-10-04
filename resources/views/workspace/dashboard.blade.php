<x-workspace heading="Dasbor">
    @php($priorities = ['urgent' => 'Mendesak', 'high' => 'Tinggi', 'normal' => 'Normal', 'low' => 'Rendah', 'P0' => 'Mendesak', 'P1' => 'Tinggi', 'P2' => 'Normal', 'P3' => 'Rendah'])
    @php($statusLabels = ['Backlog' => 'Menunggu', 'Planned' => 'Direncanakan', 'In Progress' => 'Dikerjakan', 'Review' => 'Ditinjau', 'Done' => 'Selesai'])
    @php($activityLabels = ['created task' => 'membuat tugas', 'updated task' => 'memperbarui tugas', 'created product' => 'membuat produk', 'updated product' => 'memperbarui produk', 'archived product' => 'mengarsipkan produk', 'changed product stage' => 'mengubah tahap produk', 'submitted idea' => 'mengirim ide', 'updated idea' => 'memperbarui ide', 'converted idea to product' => 'mengubah ide menjadi produk'])
    @php($dailyBoosts = [
        'Bangun dan semangat, langkah kecil hari ini tetap menghitung.',
        'Gas pelan tapi pasti, ide bagus butuh dikerjakan.',
        'Hari ini bukan untuk sempurna, tapi untuk maju.',
        'Fokus satu hal dulu, biar hasilnya punya arah.',
        'Tim hebat menang karena konsisten, bukan karena menunggu mood.',
        'Buka tugas, tutup ragu, lanjutkan yang penting.',
        'Santai boleh, berhenti jangan.',
    ])
    @php($dailyBoost = $dailyBoosts[now()->dayOfWeekIso - 1])
    <div class="brand-panel mb-8">
        <p class="text-xs font-bold tracking-widest text-cyan-200">SEMANGAT HARI INI</p>
        <div class="mt-3 flex flex-wrap items-end justify-between gap-5">
            <div>
                <h2 class="text-3xl font-black">{{ $dailyBoost }}</h2>
                <p class="mt-2 text-slate-300">Fokus minggu ini: {{ $products->first()?->name ?? 'mulai produk pertama Anda' }}</p>
            </div><a href="{{ route('products') }}"
                class="rounded-xl bg-white px-4 py-2 text-sm font-bold text-blue-800 shadow-sm transition hover:text-fuchsia-600">Lihat produk</a>
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach (['Produk aktif' => $stats['active'], 'Sedang dikerjakan' => $stats['progress'], 'Selesai' => $stats['done'], 'Terblokir' => $stats['blocked'], 'Ide' => $stats['ideas']] as $name => $value)
            <div class="card">
                <p class="text-sm text-slate-500">{{ $name }}</p>
                <p class="mt-2 text-3xl font-black">{{ $value }}</p>
            </div>
        @endforeach
    </div>
    <div class="mt-8 grid gap-7 xl:grid-cols-[1.5fr_1fr]">
        <section>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-bold">Tugas terbaru</h2><a class="brand-link text-sm"
                    href="{{ route('tasks') }}">Buka papan</a>
            </div>
            <div class="space-y-2">
                @forelse($tasks as $task)
                    @php($priorityClass = ['P0' => 'urgent', 'P1' => 'high', 'P2' => 'normal', 'P3' => 'low'][$task->priority] ?? $task->priority)
                    <div class="card flex items-center gap-3"><span
                            class="h-2.5 w-2.5 rounded-full {{ $task->is_blocked ? 'bg-rose-500' : 'bg-cyan-500' }}"></span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold">{{ $task->title }}</p>
                            <p class="text-xs text-slate-400">{{ $task->product?->name ?? 'Tanpa produk' }} ·
                                {{ $statusLabels[$task->status] ?? $task->status }}</p>
                        </div><span
                            class="badge priority-{{ $priorityClass }}">{{ $priorities[$task->priority] ?? $task->priority }}</span>
                </div>@empty<div class="card text-slate-500">Belum ada tugas. Buat pekerjaan pertama tim.</div>
                @endforelse
            </div>
        </section>
        <section>
            <h2 class="mb-3 font-bold">Aktivitas terbaru</h2>
            <div class="card space-y-4">
                @forelse($activities as $activity)
                    <div class="border-b border-slate-100 pb-3 last:border-0">
                        <p class="text-sm"><b>{{ $activity->user?->name ?? 'Sistem' }}</b> {{ $activityLabels[$activity->action] ?? $activity->action }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ $activity->created_at->diffForHumans() }}</p>
                </div>@empty<p class="text-sm text-slate-500">Aktivitas akan tampil di sini.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-workspace>
