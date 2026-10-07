<x-workspace heading="Alur Produk">
    @php($stageLabels = ['Idea' => 'Ide', 'Validation' => 'Validasi', 'Design' => 'Desain', 'Development' => 'Pengembangan', 'Content Preparation' => 'Persiapan konten', 'Ready To Launch' => 'Siap diluncurkan', 'Launched' => 'Diluncurkan', 'Growth' => 'Pertumbuhan'])
    <div class="overflow-x-auto">
        <div class="flex min-w-max gap-4">
            @foreach ($stages as $stage)
                <section class="w-72 rounded-2xl bg-slate-100 p-3">
                    <h2 class="mb-3 text-sm font-black">{{ strtoupper($stageLabels[$stage] ?? $stage) }}</h2>
                    <div class="space-y-3">
                        @forelse($products->where('stage',$stage) as $product)
                            <article class="card">
                                <p class="font-bold">{{ $product->name }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $product->audience?->name }}</p>
                                <form method="POST" action="{{ route('products.stage', $product) }}"
                                    class="mt-3 flex gap-1">@csrf @method('PATCH')<select
                                        class="field min-w-0 flex-1 text-xs" name="stage">
                                        @foreach ($stages as $option)
                                            <option value="{{ $option }}" @selected($option === $stage)>
                                                {{ $stageLabels[$option] ?? $option }}</option>
                                        @endforeach
                                    </select>
                                    <button class="brand-link text-xs">Pindah</button>
                                </form>
                        </article>@empty<p class="py-5 text-center text-xs text-slate-400">Tidak ada produk</p>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</x-workspace>
