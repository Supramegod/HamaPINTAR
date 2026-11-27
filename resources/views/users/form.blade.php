@php
    $editing = $user->exists;
    $self = $editing && $user->is(auth()->user());
@endphp

<x-layouts.app :title="$editing ? 'Ubah Pengguna' : 'Tambah Pengguna'"
               :description="$editing ? 'Perbarui data akun '.$user->name.'.' : 'Buat akun baru untuk mengakses sistem.'">
    <x-slot:actions>
        <x-button variant="secondary" icon="chevron-left" :href="route('users.index')">Kembali</x-button>
    </x-slot:actions>

    <form method="POST" action="{{ $editing ? route('users.update', $user) : route('users.store') }}" class="max-w-3xl space-y-6" data-loading-form>
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-card title="Informasi Akun" icon="user" description="Nama dan email yang dipakai untuk masuk.">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-form.input name="name" label="Nama lengkap" :value="$user->name" required />
                <x-form.input name="email" type="email" label="Email" :value="$user->email" required autocomplete="off" />
            </div>
        </x-card>

        <x-card title="Peran & Status" icon="shield" description="Menentukan hak akses pengguna.">
            <div class="space-y-5">
                <fieldset @disabled($self)>
                    <legend class="mb-2 block text-sm font-medium text-slate-700">Peran</legend>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach (\App\Enums\UserRole::cases() as $role)
                            <label class="relative flex cursor-pointer gap-3 rounded-lg p-4 ring-1 ring-slate-200 transition has-checked:bg-emerald-50/50 has-checked:ring-2 has-checked:ring-emerald-600 has-disabled:cursor-not-allowed has-disabled:opacity-60">
                                <input type="radio" name="role" value="{{ $role->value }}" class="mt-0.5 size-4 accent-emerald-600"
                                       @checked(old('role', $user->role?->value ?? \App\Enums\UserRole::Operator->value) === $role->value)>
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">{{ $role->label() }}</span>
                                    <span class="block text-xs text-slate-500">{{ $role->description() }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('role') <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p> @enderror
                </fieldset>

                <div class="border-t border-slate-100 pt-5">
                    <x-form.toggle name="is_active" label="Akun aktif" :checked="$user->is_active ?? true" :disabled="$self"
                                   description="Akun nonaktif tidak dapat masuk dan sesinya langsung diakhiri." />
                </div>

                @if ($self)
                    <x-alert tone="info">Anda tidak dapat mengubah peran atau menonaktifkan akun sendiri.</x-alert>
                @endif
            </div>
        </x-card>

        <x-card title="Kata Sandi" icon="lock" :description="$editing ? 'Kosongkan jika tidak ingin mengganti kata sandi.' : 'Minimal 8 karakter, berisi huruf dan angka.'">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-form.input name="password" type="password" label="Kata sandi" autocomplete="new-password" :required="! $editing" />
                <x-form.input name="password_confirmation" type="password" label="Ulangi kata sandi" autocomplete="new-password" :required="! $editing" />
            </div>
        </x-card>

        <div class="flex justify-end gap-3">
            <x-button variant="secondary" :href="route('users.index')">Batal</x-button>
            <x-button type="submit"><span data-label>{{ $editing ? 'Simpan Perubahan' : 'Tambah Pengguna' }}</span></x-button>
        </div>
    </form>
</x-layouts.app>
