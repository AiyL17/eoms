@extends('public.layout')

@section('title', 'Executive Orders — Public Registry')

@section('content')

{{-- ═══════════════════════════════════════════════════════════════ Hero --}}
<div class="hero-shimmer rounded-2xl overflow-hidden mb-8 relative">
    {{-- Decorative background circles --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-10 -right-10 w-64 h-64 rounded-full bg-white/5 blur-2xl"></div>
        <div class="absolute bottom-0 left-10 w-48 h-48 rounded-full bg-white/5 blur-2xl"></div>
    </div>

    <div class="relative px-6 md:px-12 py-10 md:py-12">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <p class="inline-flex items-center gap-2 text-violet-300 text-xs font-semibold uppercase tracking-widest mb-3">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>
                    Official Government Record
                </p>
                <h1 class="text-white text-3xl md:text-4xl font-bold leading-tight mb-2">Executive Order Registry</h1>
                <p class="text-violet-200/75 text-sm md:text-base max-w-xl leading-relaxed">
                    Browse, search, and download all official executive orders issued by the City Government. This portal provides public read-only access to all records.
                </p>
            </div>

            {{-- Stats row + live clock --}}
            <div class="flex flex-col items-start md:items-end gap-3 shrink-0 w-full md:w-auto">

                {{-- Live clock — visible on all screen sizes --}}
                <div class="text-left md:text-right">
                    <p id="portal-clock" class="text-white text-xl md:text-2xl font-bold tabular-nums tracking-tight leading-none"></p>
                    <p id="portal-date"  class="text-violet-300/80 text-xs font-medium mt-1"></p>
                </div>

                {{-- Stat pills — left-aligned on mobile, right-aligned on desktop --}}
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="text-center px-5 py-3 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/15">
                        <p class="text-white text-2xl font-bold">{{ number_format($totalEos) }}</p>
                        <p class="text-violet-300 text-xs font-medium mt-0.5">Total Orders</p>
                    </div>
                    <div class="text-center px-5 py-3 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/15">
                        <p class="text-white text-2xl font-bold">{{ number_format($totalActive) }}</p>
                        <p class="text-violet-300 text-xs font-medium mt-0.5">Active</p>
                    </div>
                    <div class="text-center px-5 py-3 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/15">
                        <p class="text-white text-2xl font-bold">{{ number_format($thisYearCount) }}</p>
                        <p class="text-violet-300 text-xs font-medium mt-0.5">This Year</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════ Search & Filters --}}
<div class="card mb-6 shadow-sm">
    <div class="px-6 py-5">
        {{-- #3: form submits on Enter naturally; added id for JS clear button --}}
        <form action="{{ route('public.index') }}" method="GET" id="filter-form">
            <div class="flex flex-col gap-3">
                {{-- Main search bar --}}
                <div class="relative">
                    <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </div>
                    <input type="text" name="search" id="search-input" value="{{ request('search') }}"
                           placeholder="Search by EO number, title, subject, or signatory…"
                           autocomplete="off"
                           class="form-input form-input-icon h-12! text-base! rounded-xl!">
                    @if(request('search'))
                    <button type="button"
                            aria-label="Clear search"
                            onclick="document.getElementById('search-input').value=''; document.getElementById('filter-form').submit();"
                            class="absolute inset-y-0 right-3 flex items-center px-2 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                    @endif
                </div>

                {{-- Filters row --}}
                <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center">
                    <div class="flex-1 grid grid-cols-2 md:grid-cols-3 gap-3">
                        {{-- Status filter --}}
                        <div class="relative">
                            <label for="filter-status" class="absolute -top-2 left-3 text-[10px] font-semibold text-violet-600 bg-white px-1 z-10 uppercase tracking-wider">Status</label>
                            <select id="filter-status" name="status" class="form-input rounded-xl! h-11" onchange="this.form.submit()">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Year filter --}}
                        <div class="relative">
                            <label for="filter-year" class="absolute -top-2 left-3 text-[10px] font-semibold text-violet-600 bg-white px-1 z-10 uppercase tracking-wider">Year</label>
                            <select id="filter-year" name="year" class="form-input rounded-xl! h-11" onchange="this.form.submit()">
                                <option value="">All Years</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tag filter --}}
                        @if($allTags->isNotEmpty())
                        <div class="relative col-span-2 md:col-span-1">
                            <label for="filter-tag" class="absolute -top-2 left-3 text-[10px] font-semibold text-violet-600 bg-white px-1 z-10 uppercase tracking-wider">Tag</label>
                            <select id="filter-tag" name="tag" class="form-input rounded-xl! h-11" onchange="this.form.submit()">
                                <option value="">All Tags</option>
                                @foreach($allTags as $tag)
                                    <option value="{{ $tag }}" {{ request('tag') === $tag ? 'selected' : '' }}>{{ $tag }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>

                    {{-- Search + Clear --}}
                    <div class="flex gap-2 shrink-0">
                        <button type="submit" class="btn-primary h-11 px-6 gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            Search
                        </button>
                        @if(request()->anyFilled(['search', 'status', 'year', 'tag']))
                            <a href="{{ route('public.index') }}" class="btn-secondary h-11 px-4 gap-1.5 text-slate-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                Clear
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        {{-- Active filter chips --}}
        @if(request()->anyFilled(['search', 'status', 'year', 'tag']))
        <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-slate-100">
            <span class="text-xs font-semibold text-slate-400 self-center">Active filters:</span>
            @if(request('search'))
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-violet-50 text-violet-700 text-xs font-semibold rounded-full border border-violet-200">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    "{{ request('search') }}"
                </span>
            @endif
            @if(request('status'))
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-violet-50 text-violet-700 text-xs font-semibold rounded-full border border-violet-200">
                    Status: {{ $statuses[request('status')] ?? request('status') }}
                </span>
            @endif
            @if(request('year'))
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-violet-50 text-violet-700 text-xs font-semibold rounded-full border border-violet-200">
                    Year: {{ request('year') }}
                </span>
            @endif
            @if(request('tag'))
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-violet-50 text-violet-700 text-xs font-semibold rounded-full border border-violet-200">
                    Tag: {{ request('tag') }}
                </span>
            @endif
        </div>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════ Results --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <p class="text-sm text-slate-500">
        <span class="font-semibold text-slate-800">{{ number_format($orders->total()) }}</span>
        {{ Str::plural('record', $orders->total()) }} found
        @if($orders->hasPages()) · showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} @endif
    </p>

    {{-- #4: Sort controls visible on all screen sizes --}}
    <div class="flex items-center gap-2 text-xs text-slate-400 flex-wrap">
        <span class="font-medium">Sort:</span>
        @foreach([
            'date_issued' => 'Date',
            'eo_number'   => 'EO No.',
            'status'      => 'Status',
        ] as $field => $label)
            @php
                $isActive = ($sort ?? 'date_issued') === $field;
                $newDir = ($isActive && $dir === 'desc') ? 'asc' : 'desc';
            @endphp
            <a href="{{ route('public.index', array_merge(request()->query(), ['sort' => $field, 'dir' => $newDir])) }}"
               class="px-2.5 py-1 rounded-lg font-semibold transition-colors
                      {{ $isActive ? 'bg-violet-100 text-violet-700' : 'hover:bg-slate-100 text-slate-500 hover:text-slate-700' }}">
                {{ $label }}
                @if($isActive)
                    {!! $dir === 'desc' ? '↓' : '↑' !!}
                @endif
            </a>
        @endforeach
    </div>
</div>

@if($orders->isEmpty())
{{-- Empty state --}}
<div class="card">
    <div class="py-20 text-center px-6">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
            <svg class="w-7 h-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c.621 0 1.125-.504 1.125-1.125V12.375a3.375 3.375 0 011.5-2.812m6.375-7.5a3.375 3.375 0 00-3.375 3.375M12 10.5h.008v.008H12V10.5zm0 3h.008v.008H12V13.5zm0 3h.008v.008H12V16.5z" />
            </svg>
        </div>
        <h3 class="text-base font-bold text-slate-800 mb-1">No records found</h3>
        <p class="text-sm text-slate-500 max-w-xs mx-auto mb-5">No executive orders match your current search criteria. Try adjusting your filters.</p>
        <a href="{{ route('public.index') }}" class="btn-secondary btn-sm">Clear all filters</a>
    </div>
</div>
@else

{{-- ── Mobile cards ─────────────────────────────────────────── --}}
<div class="block md:hidden space-y-3">
    @foreach($orders as $eo)
    {{-- #11: proper <a> wrapper — keyboard-navigable and screen-reader friendly --}}
    <a href="{{ route('public.show', $eo) }}"
       class="card portal-card-hover flex items-start gap-4 p-4 no-underline border border-slate-100 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:ring-offset-2">
        {{-- Status colour icon --}}
        <div class="shrink-0 mt-1">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center status-icon-bg-{{ $eo->status }}">
                <svg class="w-4 h-4 status-icon-text-{{ $eo->status }}"
                     fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
            </div>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap mb-1.5">
                <span class="eo-number-pill text-xs font-bold text-violet-700 bg-violet-50 border border-violet-100 px-2.5 py-0.5 rounded-lg">{{ $eo->eo_number }}</span>
                <span class="badge-{{ $eo->status }}">{{ $eo->status_label }}</span>
            </div>
            <p class="text-sm font-semibold text-slate-800 leading-snug line-clamp-2">{{ $eo->title }}</p>
            @if($eo->subject)
                <p class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $eo->subject }}</p>
            @endif
            <div class="flex items-center gap-3 mt-2 text-xs text-slate-400">
                <span class="flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5" /></svg>
                    {{ $eo->date_issued->format('M d, Y') }}
                </span>
                <span class="flex items-center gap-1 truncate">
                    <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                    {{ Str::limit($eo->signed_by, 28) }}
                </span>
            </div>
        </div>
        <svg class="w-4 h-4 text-slate-300 shrink-0 mt-2" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
    </a>
    @endforeach
</div>

{{-- ── Desktop table ────────────────────────────────────────── --}}
<div class="hidden md:block card overflow-hidden">
    <div class="overflow-x-auto">
        {{-- #11: replaced onclick tr with proper <a> links; table uses role="rowgroup" --}}
        <table class="w-full table-auto" style="min-width: 680px;" role="table">
            <thead role="rowgroup">
                <tr role="row">
                    <th class="pl-6! w-36" scope="col">EO Number</th>
                    <th scope="col">Title & Subject</th>
                    <th class="w-32" scope="col">Date Issued</th>
                    <th class="w-44" scope="col">Signatory</th>
                    <th class="w-32" scope="col">Status</th>
                    <th class="w-20 text-right! pr-6!" scope="col"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody role="rowgroup">
                @foreach($orders as $eo)
                {{-- #11: each row's link covers the whole row via stretched-link pattern --}}
                <tr class="group relative" role="row">
                    <td class="pl-6!" role="cell">
                        <span class="eo-number-pill inline-block text-xs font-bold text-violet-700 bg-violet-50 border border-violet-100 px-2.5 py-1 rounded-lg">{{ $eo->eo_number }}</span>
                    </td>
                    <td role="cell">
                        <div class="text-[13px] font-semibold text-slate-800 mb-0.5 group-hover:text-violet-700 transition-colors">{{ Str::limit($eo->title, 70) }}</div>
                        @if($eo->subject)
                        <div class="text-xs text-slate-400">{{ Str::limit($eo->subject, 85) }}</div>
                        @endif
                        @if($eo->tags)
                        <div class="flex flex-wrap gap-1 mt-1.5">
                            @foreach(array_slice($eo->tags, 0, 3) as $tag)
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 text-[10px] font-medium rounded-full">{{ $tag }}</span>
                            @endforeach
                            @if(count($eo->tags) > 3)
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-400 text-[10px] font-medium rounded-full">+{{ count($eo->tags) - 3 }}</span>
                            @endif
                        </div>
                        @endif
                    </td>
                    <td class="whitespace-nowrap" role="cell">
                        <p class="text-[13px] font-medium text-slate-700">{{ $eo->date_issued->format('M d, Y') }}</p>
                        <p class="text-[11px] text-slate-400">{{ $eo->date_issued->format('l') }}</p>
                    </td>
                    <td class="text-[13px] text-slate-600" role="cell">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg bg-violet-100 text-violet-600 flex items-center justify-center shrink-0 text-[10px] font-bold" aria-hidden="true">
                                {{ strtoupper(substr($eo->signed_by, 0, 1)) }}
                            </div>
                            <span class="truncate max-w-[120px]" title="{{ $eo->signed_by }}">{{ Str::limit($eo->signed_by, 22) }}</span>
                        </div>
                    </td>
                    <td role="cell"><span class="badge-{{ $eo->status }}">{{ $eo->status_label }}</span></td>
                    <td class="text-right! pr-5!" role="cell">
                        {{-- Stretched link makes the whole row clickable for pointer users
                             while remaining keyboard-accessible via this visible <a> --}}
                        <a href="{{ route('public.show', $eo) }}"
                           class="inline-flex items-center gap-1 text-xs font-semibold text-violet-600
                                  hover:text-violet-800 transition-colors px-3 py-1.5 rounded-lg
                                  hover:bg-violet-50 border border-transparent hover:border-violet-100
                                  focus:outline-none focus:ring-2 focus:ring-violet-400
                                  after:absolute after:inset-0 after:content-['']"
                           aria-label="View {{ $eo->eo_number }}">
                            View
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($orders->hasPages())
    <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/50 flex items-center justify-between gap-4 flex-wrap">
        <p class="text-xs text-slate-400">
            Showing <span class="font-semibold text-slate-600">{{ $orders->firstItem() }}</span>–<span class="font-semibold text-slate-600">{{ $orders->lastItem() }}</span>
            of <span class="font-semibold text-slate-600">{{ number_format($orders->total()) }}</span> records
        </p>
        {{ $orders->links() }}
    </div>
    @endif
</div>

{{-- #5: Mobile pagination — rendered properly below the card list --}}
@if($orders->hasPages())
<div class="block md:hidden mt-4 flex justify-center">
    {{ $orders->links() }}
</div>
@endif

@endif

@endsection

@push('scripts')
<script>
(function () {
    const clockEl = document.getElementById('portal-clock');
    const dateEl  = document.getElementById('portal-date');
    if (!clockEl || !dateEl) return;

    const days   = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    function tick() {
        const now  = new Date();
        let h      = now.getHours();
        const m    = String(now.getMinutes()).padStart(2, '0');
        const s    = String(now.getSeconds()).padStart(2, '0');
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;

        clockEl.textContent = `${h}:${m}:${s} ${ampm}`;
        dateEl.textContent  = `${days[now.getDay()]}, ${months[now.getMonth()]} ${now.getDate()}, ${now.getFullYear()}`;
    }

    tick();
    setInterval(tick, 1000);
})();
</script>
@endpush
