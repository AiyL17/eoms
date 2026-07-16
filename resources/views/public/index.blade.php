@extends('public.layout')

@section('title', 'Executive Orders — Public Registry')

@section('content')

{{-- Hero Banner --}}
<div class="rounded-2xl px-6 py-7 mb-7 text-center"
     style="background: linear-gradient(135deg, #3d1f8a 0%, #5b21b6 60%, #6d28d9 100%);">
    <p class="text-violet-300 text-xs font-semibold uppercase tracking-widest mb-2">City Government</p>
    <h1 class="text-white text-2xl font-bold mb-2">Executive Order Registry</h1>
    <p class="text-violet-200/80 text-sm mb-5">Browse and download official executive orders issued by the City Government.</p>
    <div class="flex items-center justify-center gap-6 flex-wrap">
        <div class="text-center">
            <p class="text-white text-xl font-bold">{{ number_format($totalEos) }}</p>
            <p class="text-violet-300 text-xs font-medium">Total Orders</p>
        </div>
        <div class="w-px h-8 bg-white/20"></div>
        <div class="text-center">
            <p class="text-white text-xl font-bold">{{ number_format($totalActive) }}</p>
            <p class="text-violet-300 text-xs font-medium">Active Orders</p>
        </div>
        <div class="w-px h-8 bg-white/20"></div>
        <div class="text-center">
            <p class="text-white text-xl font-bold">{{ date('Y') }}</p>
            <p class="text-violet-300 text-xs font-medium">Current Year</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-5">
    <div class="px-5 py-4">
        <form action="{{ route('public.index') }}" method="GET">
            <div class="flex flex-col lg:flex-row gap-3 items-center">
                <div class="flex-1 w-full relative">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by EO number, title, subject, signatory…"
                           class="form-input form-input-icon">
                </div>
                <div class="w-full lg:w-44 shrink-0">
                    <select name="status" class="form-input">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full lg:w-36 shrink-0">
                    <select name="year" class="form-input">
                        <option value="">All Years</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                @if($allTags->isNotEmpty())
                <div class="w-full lg:w-44 shrink-0">
                    <select name="tag" class="form-input">
                        <option value="">All Tags</option>
                        @foreach($allTags as $tag)
                            <option value="{{ $tag }}" {{ request('tag') === $tag ? 'selected' : '' }}>{{ $tag }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="flex gap-2 w-full lg:w-auto shrink-0">
                    <button type="submit" class="btn-primary h-[42px] px-5 w-full lg:w-auto">Search</button>
                    @if(request()->anyFilled(['search', 'status', 'year', 'tag']))
                        <a href="{{ route('public.index') }}" class="btn-secondary h-[42px] px-4 w-full lg:w-auto text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Results --}}
<div class="card">
    <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-4 flex-wrap">
        <p class="text-xs font-semibold text-slate-500">{{ number_format($orders->total()) }} {{ Str::plural('record', $orders->total()) }} found</p>
    </div>

    {{-- Mobile cards --}}
    <div class="block md:hidden divide-y divide-slate-100">
        @forelse($orders as $eo)
        <a href="{{ route('public.show', $eo) }}" class="flex items-start gap-3 px-4 py-4 hover:bg-violet-50/40 transition-colors">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span class="text-xs font-bold text-violet-700 bg-violet-50 border border-violet-100 px-2 py-0.5 rounded-lg font-mono">{{ $eo->eo_number }}</span>
                    <span class="badge-{{ $eo->status }}">{{ $eo->status_label }}</span>
                </div>
                <p class="text-sm font-semibold text-slate-800 leading-snug">{{ Str::limit($eo->title, 70) }}</p>
                <p class="text-xs text-slate-400 mt-0.5">{{ $eo->date_issued->format('M d, Y') }} · {{ Str::limit($eo->signed_by, 30) }}</p>
            </div>
            <svg class="w-4 h-4 text-slate-300 shrink-0 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
        </a>
        @empty
        <div class="py-16 text-center px-4">
            <p class="text-sm font-bold text-slate-800 mb-1">No records found</p>
            <p class="text-sm text-slate-500">No executive orders match your current search.</p>
        </div>
        @endforelse
    </div>

    {{-- Desktop table --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full table-auto table-wide">
            <thead>
                <tr>
                    <th class="w-36">EO Number</th>
                    <th>Title & Subject</th>
                    <th class="w-32">Date Issued</th>
                    <th class="w-40">Signatory</th>
                    <th class="w-32">Status</th>
                    <th class="w-24 text-right pr-6">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $eo)
                <tr class="group cursor-pointer" onclick="window.location='{{ route('public.show', $eo) }}'">
                    <td class="font-bold text-slate-800 whitespace-nowrap">{{ $eo->eo_number }}</td>
                    <td>
                        <div class="text-[13px] font-semibold text-slate-800 mb-0.5 group-hover:text-violet-700 transition-colors truncate max-w-xs">{{ Str::limit($eo->title, 65) }}</div>
                        <div class="text-xs text-slate-400 truncate max-w-xs">{{ Str::limit($eo->subject, 80) }}</div>
                    </td>
                    <td class="text-slate-500 whitespace-nowrap text-[13px]">{{ $eo->date_issued->format('M d, Y') }}</td>
                    <td class="text-slate-600 text-[13px] whitespace-nowrap truncate" title="{{ $eo->signed_by }}">{{ Str::limit($eo->signed_by, 22) }}</td>
                    <td class="whitespace-nowrap"><span class="badge-{{ $eo->status }}">{{ $eo->status_label }}</span></td>
                    <td class="text-right pr-5">
                        <a href="{{ route('public.show', $eo) }}"
                           class="inline-flex items-center gap-1 text-xs font-semibold text-violet-600 hover:text-violet-800 transition-colors px-2 py-1 rounded-lg hover:bg-violet-50"
                           onclick="event.stopPropagation()">
                            View
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-16 text-center">
                        <p class="text-sm font-bold text-slate-800 mb-1">No records found</p>
                        <p class="text-sm text-slate-500">Try adjusting your search filters.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
    <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/50 flex items-center justify-between gap-4 flex-wrap">
        <p class="text-xs text-slate-400">Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ number_format($orders->total()) }}</p>
        {{ $orders->links() }}
    </div>
    @endif
</div>

@endsection
