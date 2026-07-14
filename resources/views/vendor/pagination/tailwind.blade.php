@if ($paginator->hasPages())
<nav role="navigation" aria-label="Pagination" class="flex items-center justify-between gap-4 flex-wrap">

    {{-- Mobile: Prev / Next only --}}
    <div class="flex items-center gap-2 sm:hidden">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 bg-slate-50 border border-slate-100 cursor-not-allowed">
                &laquo; Prev
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-violet-50 hover:text-violet-700 hover:border-violet-200 transition-all">
                &laquo; Prev
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-violet-50 hover:text-violet-700 hover:border-violet-200 transition-all">
                Next &raquo;
            </a>
        @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 bg-slate-50 border border-slate-100 cursor-not-allowed">
                Next &raquo;
            </span>
        @endif
    </div>

    {{-- Desktop: full page list --}}
    <div class="hidden sm:flex items-center gap-1">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-300 bg-slate-50 border border-slate-100 cursor-not-allowed">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 bg-white border border-slate-200 hover:bg-violet-50 hover:text-violet-700 hover:border-violet-200 transition-all"
               rel="prev">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </a>
        @endif

        {{-- Page numbers --}}
        @foreach ($elements as $element)

            {{-- Dots --}}
            @if (is_string($element))
                <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-medium text-slate-400">
                    &hellip;
                </span>
            @endif

            {{-- Page links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-bold text-white bg-violet-600 border border-violet-600 shadow-sm shadow-violet-600/20">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-violet-50 hover:text-violet-700 hover:border-violet-200 transition-all">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif

        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 bg-white border border-slate-200 hover:bg-violet-50 hover:text-violet-700 hover:border-violet-200 transition-all"
               rel="next">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        @else
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-300 bg-slate-50 border border-slate-100 cursor-not-allowed">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </span>
        @endif

    </div>
</nav>
@endif
