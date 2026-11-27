<x-layouts.guest title="Masuk">
    <div class="flex items-center gap-3 lg:hidden">
        <span class="grid size-10 place-items-center rounded-lg bg-emerald-600 text-white"><x-icon name="signal" /></span>
        <span class="text-lg font-bold tracking-tight text-slate-900">Hama<span class="text-emerald-600">PINTAR</span></span>
    </div>

    <h1 class="mt-8 text-2xl font-bold tracking-tight text-slate-900 lg:mt-0">Masuk ke akun Anda</h1>
    <p class="mt-2 text-sm text-slate-500">Gunakan akun yang diberikan oleh administrator.</p>

    <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5" data-loading-form>
        @csrf

        <x-form.input name="email" type="email" label="Email" autocomplete="username" required autofocus placeholder="nama@contoh.id" />
        <x-form.input name="password" type="password" label="Kata sandi" autocomplete="current-password" required />

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" class="size-4 rounded accent-emerald-600" @checked(old('remember'))>
            Ingat saya
        </label>

        <x-button type="submit" class="w-full py-2.5"><span data-label>Masuk</span></x-button>
    </form>

    @env('local')
        <div class="mt-8 rounded-lg bg-amber-50 p-4 text-xs text-amber-800 ring-1 ring-amber-200 ring-inset">
            <p class="font-semibold">Akun demo (hanya tampil di lingkungan lokal)</p>
            <p class="mt-1">Admin: <span class="font-mono">admin@hamapintar.test</span> · Petugas: <span class="font-mono">petugas@hamapintar.test</span></p>
            <p>Kata sandi: <span class="font-mono">password</span></p>
        </div>
    @endenv
</x-layouts.guest>
