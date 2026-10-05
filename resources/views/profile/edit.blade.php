<x-workspace heading="Pengaturan Akun">
    <div class="mx-auto max-w-3xl space-y-4 sm:space-y-6">
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
