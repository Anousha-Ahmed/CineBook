@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="mt-10 flex items-center justify-between gap-4">
        <p class="text-xs text-slate-500">
            Showing <span class="font-semibold text-slate-300">{{ $paginator->firstItem() }}</span>
            to <span class="font-semibold text-slate-300">{{ $paginator->lastItem() }}</span>
            of <span class="font-semibold text-slate-300">{{ $paginator->total() }}</span>
        </p>

        <div class="flex items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/[.06] text-slate-600">&larr;</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 text-slate-300 transition hover:border-white/25 hover:text-white">&larr;</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-1 text-sm text-slate-600">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="flex h-9 min-w-9 items-center justify-center rounded-lg bg-brand-600 px-2 text-sm font-semibold text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                               class="flex h-9 min-w-9 items-center justify-center rounded-lg border border-white/10 px-2 text-sm text-slate-300 transition hover:border-white/25 hover:text-white">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 text-slate-300 transition hover:border-white/25 hover:text-white">&rarr;</a>
            @else
                <span class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/[.06] text-slate-600">&rarr;</span>
            @endif
        </div>
    </nav>
@endif
