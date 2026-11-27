@props(['title'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} · {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans text-slate-700 antialiased">
<div class="flex min-h-full">
    {{-- Panel brand --}}
    <aside class="relative hidden w-0 flex-1 overflow-hidden bg-emerald-950 lg:flex lg:flex-col lg:justify-between lg:p-12">
        <div class="pointer-events-none absolute -top-24 -right-24 size-96 rounded-full bg-emerald-500/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-16 size-96 rounded-full bg-lime-400/10 blur-3xl"></div>

        <div class="relative flex items-center gap-3">
            <span class="grid size-10 place-items-center rounded-lg bg-emerald-500 text-white"><x-icon name="signal" /></span>
            <span class="text-lg font-bold tracking-tight text-white">Hama<span class="text-emerald-400">PINTAR</span></span>
        </div>

        <div class="relative max-w-md">
            <h2 class="text-3xl font-bold leading-tight tracking-tight text-white">Pantau serangan tikus di sawah secara terukur.</h2>
            <p class="mt-4 text-emerald-100/70">Data sensor PIR dipetakan, dianalisis per jam dan per hari, lalu diproyeksikan untuk membantu menentukan titik jebakan.</p>

            <ul class="mt-8 space-y-4">
                @foreach ([['map', 'Peta sebaran deteksi per node sensor'], ['chart', 'Analisis pola jam & hari serangan'], ['sparkles', 'Prediksi tren beberapa hari ke depan']] as [$icon, $text])
                    <li class="flex items-center gap-3 text-sm text-emerald-50">
                        <span class="grid size-8 place-items-center rounded-lg bg-white/10 text-emerald-300"><x-icon :name="$icon" class="size-4" /></span>
                        {{ $text }}
                    </li>
                @endforeach
            </ul>
        </div>

        <p class="relative text-xs text-emerald-200/50">© {{ now()->year }} {{ config('app.name') }}</p>
    </aside>

    {{-- Form --}}
    <main class="flex flex-1 flex-col justify-center px-6 py-12 lg:flex-none lg:px-20 xl:px-24">
        <div class="mx-auto w-full max-w-sm">
            {{ $slot }}
        </div>
    </main>
</div>
</body>
</html>
