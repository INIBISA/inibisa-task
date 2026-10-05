<x-workspace heading="Dasbor">
    @php($priorities = ['urgent' => 'Mendesak', 'high' => 'Tinggi', 'normal' => 'Normal', 'low' => 'Rendah', 'P0' => 'Mendesak', 'P1' => 'Tinggi', 'P2' => 'Normal', 'P3' => 'Rendah'])
    @php($statusLabels = ['Backlog' => 'Menunggu', 'Planned' => 'Direncanakan', 'In Progress' => 'Dikerjakan', 'Review' => 'Ditinjau', 'Done' => 'Selesai'])
    @php($statusClasses = ['Backlog' => 'bg-slate-100 text-slate-700', 'Planned' => 'bg-blue-100 text-blue-700', 'In Progress' => 'bg-cyan-100 text-cyan-700', 'Review' => 'bg-violet-100 text-violet-700', 'Done' => 'bg-emerald-100 text-emerald-700'])
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
    <div class="brand-panel mb-6 sm:mb-8">
        <p class="text-xs font-bold tracking-widest text-cyan-200">SEMANGAT HARI INI</p>
        <div class="mt-3 flex flex-wrap items-end justify-between gap-5">
            <div>
                <h2 class="text-2xl font-black sm:text-3xl">{{ $dailyBoost }}</h2>
                <p class="mt-2 text-slate-300">Fokus minggu ini: {{ $products->first()?->name ?? 'mulai produk pertama Anda' }}</p>
            </div><a href="{{ route('products') }}"
                class="rounded-xl bg-white px-4 py-2 text-sm font-bold text-blue-800 shadow-sm transition hover:text-fuchsia-600">Lihat produk</a>
        </div>
    </div>
    <div class="dashboard-stats grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-5">
        @foreach (['Produk aktif' => $stats['active'], 'Sedang dikerjakan' => $stats['progress'], 'Selesai' => $stats['done'], 'Terblokir' => $stats['blocked'], 'Ide' => $stats['ideas']] as $name => $value)
            <div class="card">
                <p class="text-sm text-slate-500">{{ $name }}</p>
                <p class="mt-2 text-3xl font-black">{{ $value }}</p>
            </div>
        @endforeach
    </div>
    <section class="mt-8">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="font-bold">Tugas per anggota</h2><a class="brand-link text-sm" href="{{ route('tasks') }}">Buka papan</a>
        </div>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($members as $member)
                @php($memberTasks = $member->assignedTasks)
                @php($total = $memberTasks->count())
                @php($done = $memberTasks->where('status', 'Done')->count())
                @php($activeTasks = $memberTasks->where('status', '!=', 'Done')->count())
                @php($blockedTasks = $memberTasks->where('is_blocked', true)->count())
                @php($progress = $total ? round($done / $total * 100) : 0)
                @php($avatarUrl = $member->avatar ? (str($member->avatar)->startsWith(['http://', 'https://', '/']) ? $member->avatar : asset('storage/'.$member->avatar)) : null)
                <article class="member-workload-card">
                    <div class="flex items-start gap-3">
                        <div class="relative shrink-0">
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="{{ $member->name }}" class="h-12 w-12 rounded-2xl object-cover ring-2 ring-white">
                            @else
                                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-cyan-100 via-blue-100 to-fuchsia-100 text-base font-black text-blue-900 ring-2 ring-white">{{ str($member->name)->substr(0, 1) }}</div>
                            @endif
                            <span class="absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 rounded-full border-2 border-white {{ $member->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="truncate text-base font-black text-slate-900">{{ $member->name }}</h3>
                                    <p class="mt-0.5 truncate text-xs font-semibold text-slate-500">{{ $member->position ?: ($member->role === 'admin' ? 'Admin' : 'Anggota') }}</p>
                                </div>
                                <span class="member-status-pill {{ $member->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-slate-100 text-slate-600 ring-slate-200' }}">{{ $member->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </div>
                            <div class="mt-3 grid grid-cols-3 gap-2">
                                <div class="member-mini-stat"><span>{{ $total }}</span><p>Total</p></div>
                                <div class="member-mini-stat"><span>{{ $activeTasks }}</span><p>Aktif</p></div>
                                <div class="member-mini-stat"><span>{{ $blockedTasks }}</span><p>Blokir</p></div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5">
                        <div class="mb-2 flex justify-between text-xs"><span class="font-bold uppercase tracking-wide text-slate-400">Progress</span><span class="font-black text-slate-800">{{ $done }}/{{ $total }} selesai · {{ $progress }}%</span></div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-100"><div class="brand-progress h-full rounded-full" style="width: {{ $progress }}%"></div></div>
                    </div>
                    <div class="mt-5 space-y-2.5 border-t border-slate-100 pt-4">
                        @forelse($memberTasks->take(4) as $task)
                            @php($priorityClass = ['P0' => 'urgent', 'P1' => 'high', 'P2' => 'normal', 'P3' => 'low'][$task->priority] ?? $task->priority)
                            <div class="member-task-row">
                                <span class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $task->is_blocked ? 'bg-rose-500' : 'bg-cyan-500' }}"></span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-bold text-slate-800">{{ $task->title }}</p>
                                    <p class="truncate text-xs font-medium text-slate-400">{{ $task->product?->name ?? 'Tanpa produk' }}</p>
                                </div>
                                <div class="flex shrink-0 flex-col items-end gap-1"><span class="badge {{ $statusClasses[$task->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $statusLabels[$task->status] ?? $task->status }}</span><span class="badge priority-{{ $priorityClass }}">{{ $priorities[$task->priority] ?? $task->priority }}</span></div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-3 py-4 text-center text-sm font-semibold text-slate-400">Belum ada tugas.</div>
                        @endforelse
                        @if($memberTasks->count() > 4)
                            <a href="{{ route('tasks') }}" class="block rounded-xl bg-slate-50 px-3 py-2 text-center text-xs font-black text-blue-700 transition hover:bg-blue-50">+{{ $memberTasks->count() - 4 }} tugas lain</a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="card text-slate-500">Belum ada anggota.</div>
            @endforelse
        </div>
    </section>
</x-workspace>
