<x-layouts.app title="Pengguna" description="Kelola akun yang dapat mengakses sistem HamaPINTAR.">
    <x-slot:actions>
        <x-button icon="plus" :href="route('users.create')">Tambah Pengguna</x-button>
    </x-slot:actions>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat label="Total Pengguna" icon="users" color="emerald" :value="$counts['total']" />
        <x-stat label="Administrator" icon="shield" color="indigo" :value="$counts['admin']" />
        <x-stat label="Akun Nonaktif" icon="lock" color="amber" :value="$counts['inactive']" />
    </section>

    <x-card :padded="false">
        <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap items-center gap-3 border-b border-slate-100 p-4">
            <div class="relative min-w-60 flex-1">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau email…"
                       class="block w-full rounded-lg border-0 py-2 pr-3 pl-9 text-sm text-slate-900 shadow-xs ring-1 ring-slate-300 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-emerald-600 focus:outline-none">
            </div>
            <div class="w-44">
                <x-form.select name="role" :options="\App\Enums\UserRole::options()" :value="request('role')" placeholder="Semua peran" />
            </div>
            <x-button type="submit" variant="secondary" icon="funnel">Filter</x-button>
            @if (request()->hasAny(['q', 'role']))
                <x-button variant="ghost" :href="route('users.index')">Reset</x-button>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th scope="col" class="px-5 py-3">Pengguna</th>
                        <th scope="col" class="px-5 py-3">Peran</th>
                        <th scope="col" class="px-5 py-3">Status</th>
                        <th scope="col" class="px-5 py-3">Terakhir Masuk</th>
                        <th scope="col" class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($users as $user)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <x-avatar :user="$user" />
                                    <div class="min-w-0">
                                        <p class="font-medium text-slate-900">
                                            {{ $user->name }}
                                            @if ($user->is(auth()->user()))
                                                <span class="ml-1 text-xs font-normal text-slate-400">(Anda)</span>
                                            @endif
                                        </p>
                                        <p class="truncate text-slate-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3">
                                <x-badge :tone="$user->isAdmin() ? 'success' : 'neutral'">{{ $user->role->label() }}</x-badge>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3">
                                @if ($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 text-emerald-700"><span class="size-1.5 rounded-full bg-emerald-500"></span>Aktif</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-slate-500"><span class="size-1.5 rounded-full bg-slate-400"></span>Nonaktif</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-slate-500">
                                {{ $user->last_login_at?->diffForHumans() ?? 'Belum pernah' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('users.edit', $user) }}" class="rounded-md p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" aria-label="Ubah {{ $user->name }}">
                                        <x-icon name="pencil" class="size-4" />
                                    </a>
                                    @can('modify-account-status', $user)
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" data-confirm="Hapus pengguna {{ $user->name }}? Tindakan ini tidak dapat dibatalkan.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600" aria-label="Hapus {{ $user->name }}">
                                                <x-icon name="trash" class="size-4" />
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state icon="users" title="Pengguna tidak ditemukan" description="Coba ubah kata kunci atau filter peran." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links() }}
    </x-card>
</x-layouts.app>
