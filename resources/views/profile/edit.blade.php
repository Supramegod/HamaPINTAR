<x-layouts.app title="Profil Saya" description="Kelola informasi akun dan kata sandi Anda.">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card class="lg:col-span-1 self-start">
            <div class="flex flex-col items-center text-center">
                <x-avatar :user="$user" size="size-20" class="text-2xl" />
                <p class="mt-4 text-lg font-semibold text-slate-900">{{ $user->name }}</p>
                <p class="text-sm text-slate-500">{{ $user->email }}</p>
                <x-badge :tone="$user->isAdmin() ? 'success' : 'neutral'" class="mt-3">{{ $user->role->label() }}</x-badge>
            </div>
            <dl class="mt-6 space-y-3 border-t border-slate-100 pt-5 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Terdaftar</dt><dd class="text-slate-900">{{ $user->created_at->translatedFormat('d M Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Terakhir masuk</dt><dd class="text-slate-900">{{ $user->last_login_at?->translatedFormat('d M Y H:i') ?? '—' }}</dd></div>
            </dl>
        </x-card>

        <div class="space-y-6 lg:col-span-2">
            <form method="POST" action="{{ route('profile.update') }}" data-loading-form>
                @csrf
                @method('PUT')
                <x-card title="Informasi Profil" icon="user" description="Nama dan email akun Anda.">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <x-form.input name="name" label="Nama lengkap" :value="$user->name" required />
                        <x-form.input name="email" type="email" label="Email" :value="$user->email" required />
                    </div>
                    <div class="mt-5 flex justify-end">
                        <x-button type="submit"><span data-label>Simpan</span></x-button>
                    </div>
                </x-card>
            </form>

            <form method="POST" action="{{ route('profile.password') }}" data-loading-form>
                @csrf
                @method('PUT')
                <x-card title="Ganti Kata Sandi" icon="lock" description="Minimal 8 karakter, berisi huruf dan angka.">
                    <div class="space-y-5">
                        <x-form.input name="current_password" type="password" label="Kata sandi saat ini" bag="password" autocomplete="current-password" required />
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <x-form.input name="password" type="password" label="Kata sandi baru" bag="password" id="new_password" autocomplete="new-password" required />
                            <x-form.input name="password_confirmation" type="password" label="Ulangi kata sandi baru" bag="password" autocomplete="new-password" required />
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end">
                        <x-button type="submit"><span data-label>Ganti Kata Sandi</span></x-button>
                    </div>
                </x-card>
            </form>
        </div>
    </div>
</x-layouts.app>
