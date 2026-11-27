@if ($paginator->hasPages())
    <nav class="flex items-center justify-between gap-4 border-t border-slate-100 px-5 py-3" aria-label="Navigasi halaman">
        <p class="hidden text-sm text-slate-500 sm:block">
            Menampilkan <span class="font-medium text-slate-900">{{ $paginator->firstItem() }}</span>–<span class="font-medium text-slate-900">{{ $paginator->lastItem() }}</span>
            dari <span class="font-medium text-slate-900">{{ number_format($paginator->total(), 0, ',', '.') }}</span> data
        </p>

        @php($base = 'inline-flex min-w-9 items-center justify-center rounded-lg px-2.5 py-1.5 text-sm font-medium')
        <div class="flex flex-1 items-center justify-between gap-1 sm:flex-none sm:justify-end">
            @if ($paginator->onFirstPage())
                <span class="{{ $base }} cursor-not-allowed text-slate-300"><x-icon name="chevron-left" class="size-4" /></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $base }} text-slate-600 hover:bg-slate-100" aria-label="Sebelumnya"><x-icon name="chevron-left" class="size-4" /></a>
            @endif

            <div class="hidden items-center gap-1 sm:flex">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="{{ $base }} text-slate-400">{{ $element }}</span>
                    @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="{{ $base }} bg-emerald-600 text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="{{ $base }} text-slate-600 hover:bg-slate-100">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>
            <span class="text-sm text-slate-500 sm:hidden">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $base }} text-slate-600 hover:bg-slate-100" aria-label="Berikutnya"><x-icon name="chevron-right" class="size-4" /></a>
            @else
                <span class="{{ $base }} cursor-not-allowed text-slate-300"><x-icon name="chevron-right" class="size-4" /></span>
            @endif
        </div>
    </nav>
@endif
