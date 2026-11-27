@props(['title', 'description' => null])

@php
    $user = auth()->user();
    // Periode filter ikut terbawa saat pindah antar halaman monitoring.
    $period = request()->only(['start_date', 'end_date']);

    $navigation = [
        'Monitoring' => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home', 'keep' => true],
            ['label' => 'Peta Sebaran', 'route' => 'map', 'icon' => 'map', 'keep' => true],
            ['label' => 'Analisis Tren', 'route' => 'analytics', 'icon' => 'chart', 'keep' => true],
        ],
        'Data' => [
            ['label' => 'Data Deteksi', 'route' => 'detections.index', 'icon' => 'list', 'keep' => true],
            ['label' => 'Riwayat Unggahan', 'route' => 'detections.imports.index', 'icon' => 'upload'],
        ],
        'Administrasi' => [
            ['label' => 'Pengguna', 'route' => 'users.index', 'icon' => 'users', 'active' => 'users.*', 'can' => 'manage-users'],
        ],
    ];

    $section = collect($navigation)->search(fn ($items) => collect($items)->contains(
        fn ($item) => request()->routeIs($item['active'] ?? $item['route'])
    )) ?: 'Akun';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans text-slate-700 antialiased">

<div data-sidebar-overlay class="fixed inset-0 z-[1190] hidden bg-slate-900/50 lg:hidden"></div>

{{-- Sidebar --}}
<aside data-sidebar class="fixed inset-y-0 left-0 z-[1200] flex w-64 -translate-x-full flex-col bg-emerald-950 transition-transform duration-200 lg:translate-x-0">
    <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <span class="grid size-9 place-items-center rounded-lg bg-emerald-500 text-white">
                <x-icon name="signal" />
            </span>
            <span class="leading-tight">
                <span class="block text-base font-bold tracking-tight text-white">Hama<span class="text-emerald-400">PINTAR</span></span>
                <span class="block text-[11px] text-emerald-200/70">Monitoring Hama Sawah</span>
            </span>
        </a>
        <button type="button" data-sidebar-close class="ml-auto rounded-md p-1 text-emerald-200 hover:bg-white/10 lg:hidden" aria-label="Tutup menu">
            <x-icon name="x" />
        </button>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-6" aria-label="Menu utama">
        @foreach ($navigation as $group => $items)
            @php($items = array_filter($items, fn ($item) => ! isset($item['can']) || $user->can($item['can'])))
            @continue(empty($items))

            <div>
                <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-emerald-300/50">{{ $group }}</p>
                <ul class="space-y-1">
                    @foreach ($items as $item)
                        @php($active = request()->routeIs($item['active'] ?? $item['route']))
                        <li>
                            <a href="{{ route($item['route'], ($item['keep'] ?? false) ? $period : []) }}"
                               @if ($active) aria-current="page" @endif
                               @class([
                                   'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                                   'bg-white/10 text-white' => $active,
                                   'text-emerald-100/75 hover:bg-white/5 hover:text-white' => ! $active,
                               ])>
                                <x-icon :name="$item['icon']" @class(['text-emerald-300' => $active, 'text-emerald-300/60 group-hover:text-emerald-300' => ! $active]) />
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    {{-- Menu pengguna --}}
    <div class="shrink-0 border-t border-white/10 p-3">
        <a href="{{ route('profile.edit') }}"
           @class(['flex items-center gap-3 rounded-lg p-2 transition', 'bg-white/10' => request()->routeIs('profile.*'), 'hover:bg-white/5' => ! request()->routeIs('profile.*')])>
            <x-avatar :user="$user" class="!ring-emerald-900" />
            <span class="min-w-0 flex-1 leading-tight">
                <span class="block truncate text-sm font-semibold text-white">{{ $user->name }}</span>
                <span class="block truncate text-xs text-emerald-200/70">{{ $user->role->label() }}</span>
            </span>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="mt-1">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-emerald-100/75 transition hover:bg-white/5 hover:text-white">
                <x-icon name="logout" class="text-emerald-300/60" /> Keluar
            </button>
        </form>
    </div>
</aside>

<div class="lg:pl-64">
    {{-- Top bar --}}
    <header class="sticky top-0 z-[1100] flex h-16 items-center gap-4 border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-6 lg:px-8">
        <button type="button" data-sidebar-open class="-m-1.5 rounded-md p-1.5 text-slate-600 hover:bg-slate-100 lg:hidden" aria-label="Buka menu">
            <x-icon name="menu" />
        </button>

        <nav class="flex min-w-0 items-center gap-2 text-sm" aria-label="Breadcrumb">
            <span class="hidden text-slate-500 sm:inline">{{ $section }}</span>
            <x-icon name="chevron-right" class="hidden size-3.5 text-slate-300 sm:block" />
            <span class="truncate font-medium text-slate-900">{{ $title }}</span>
        </nav>

        <div class="ml-auto flex items-center gap-3">
            <span class="hidden text-sm text-slate-500 md:inline">{{ now()->translatedFormat('l, d F Y') }}</span>
        </div>
    </header>

    <main class="px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-[1440px] space-y-6">
            {{-- Judul halaman --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $title }}</h1>
                    @if ($description)
                        <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
                    @endif
                </div>
                @isset($actions)
                    <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
                @endisset
            </div>

            @isset($toolbar)
                {{ $toolbar }}
            @endisset

            <x-flash />

            {{ $slot }}
        </div>
    </main>

    <footer class="px-4 pb-8 text-center text-xs text-slate-400 sm:px-6 lg:px-8">
        © {{ now()->year }} {{ config('app.name') }} · WebGIS Monitoring Hama Tikus Sawah
    </footer>
</div>

@include('detections.partials.upload-dialog')
{{ $modals ?? '' }}
</body>
</html>
