<x-workspace heading="Pengaturan Akun">
    <div class="mx-auto max-w-3xl space-y-4 sm:space-y-6">
            <div class="card p-5 sm:p-8">
                <h2 class="text-lg font-bold">Notifikasi tugas</h2>
                <p class="mt-2 text-sm text-slate-500">Dapatkan pemberitahuan saat tugas yang Anda buat atau terima ditambahkan dan diperbarui.</p>
                <p data-push-status role="status" class="mt-3 text-sm text-slate-500">Memeriksa status notifikasi…</p>
                <div class="mt-4 flex flex-wrap gap-2"><button type="button" data-push-toggle class="brand-button min-h-11">Aktifkan notifikasi</button><button type="button" data-push-test class="action-button min-h-11"><i data-lucide="bell"></i> Kirim notifikasi tes</button></div>
            </div>
            <div class="card p-5 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="card p-5 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="card p-5 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
    </div>
</x-workspace>
