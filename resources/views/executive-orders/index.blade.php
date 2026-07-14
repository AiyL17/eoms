@extends('layouts.app')

@section('title', 'Executive Orders')
@section('page-title', 'Executive Orders')

@section('header-actions')
    <a href="{{ route('executive-orders.create') }}" class="btn-primary btn-sm">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Upload New EO
    </a>
@endsection

@section('content')

{{-- Filters --}}
<div class="card mb-5">
    <div class="px-5 py-4">
        <form action="{{ route('executive-orders.index') }}" method="GET">
            <div class="flex flex-col lg:flex-row gap-3 items-center">

                {{-- Search --}}
                <div class="flex-1 w-full relative">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Search by EO number, title, subject, signatory…"
                           class="form-input form-input-icon">
                </div>

                {{-- Status --}}
                <div class="w-full lg:w-44 shrink-0">
                    <select name="status" id="status" class="form-input">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Year --}}
                <div class="w-full lg:w-36 shrink-0">
                    <select name="year" id="year" class="form-input">
                        <option value="">All Years</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Actions --}}
                <div class="flex gap-2 w-full lg:w-auto shrink-0">
                    <button type="submit" class="btn-primary h-[42px] px-5 w-full lg:w-auto">
                        Filter
                    </button>
                    @if(request()->anyFilled(['search', 'status', 'year']))
                        <a href="{{ route('executive-orders.index') }}"
                           class="btn-secondary h-[42px] px-4 w-full lg:w-auto text-slate-400 hover:text-slate-600"
                           title="Clear filters">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">

    {{-- Result count + active filter chips --}}
    <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-4 flex-wrap">
        <p class="text-xs font-semibold text-slate-500">
            {{ number_format($orders->total()) }} {{ Str::plural('record', $orders->total()) }} found
            @if($orders->total() !== $orders->total() - 0)@endif
        </p>
        @if(request()->anyFilled(['search', 'status', 'year']))
        <div class="flex items-center gap-2 flex-wrap">
            @if(request('search'))
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-violet-50 text-violet-700 text-xs font-medium rounded-full border border-violet-100">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    "{{ Str::limit(request('search'), 30) }}"
                </span>
            @endif
            @if(request('status'))
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-violet-50 text-violet-700 text-xs font-medium rounded-full border border-violet-100">
                    Status: {{ $statuses[request('status')] ?? request('status') }}
                </span>
            @endif
            @if(request('year'))
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-violet-50 text-violet-700 text-xs font-medium rounded-full border border-violet-100">
                    Year: {{ request('year') }}
                </span>
            @endif
        </div>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full table-auto table-wide">
            <thead>
                <tr>
                    <th class="w-36">EO Number</th>
                    <th>Title & Subject</th>
                    <th class="w-32">Date Issued</th>
                    <th class="w-40">Signatory</th>
                    <th class="w-28">Status</th>
                    <th class="w-24 text-right pr-6">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $eo)
                <tr class="group cursor-pointer" onclick="window.location='{{ route('executive-orders.show', $eo) }}'">
                    <td class="font-bold text-slate-800 whitespace-nowrap">{{ $eo->eo_number }}</td>
                    <td>
                        <div class="text-[13px] font-semibold text-slate-800 mb-0.5 group-hover:text-violet-700 transition-colors truncate max-w-xs" title="{{ $eo->title }}">
                            {{ Str::limit($eo->title, 65) }}
                        </div>
                        <div class="text-xs text-slate-400 truncate max-w-xs" title="{{ $eo->subject }}">
                            {{ Str::limit($eo->subject, 80) }}
                        </div>
                    </td>
                    <td class="text-slate-500 whitespace-nowrap text-[13px]">{{ $eo->date_issued->format('M d, Y') }}</td>
                    <td class="text-slate-600 text-[13px] whitespace-nowrap truncate" title="{{ $eo->signed_by }}">
                        {{ Str::limit($eo->signed_by, 22) }}
                    </td>
                    <td>
                        <span class="badge-{{ $eo->status }}">{{ $eo->status_label }}</span>
                    </td>
                    <td class="text-right pr-5">
                        <a href="{{ route('executive-orders.show', $eo) }}"
                           class="inline-flex items-center gap-1 text-xs font-semibold text-violet-600 hover:text-violet-800 transition-colors px-2 py-1 rounded-lg hover:bg-violet-50"
                           onclick="event.stopPropagation()">
                            View
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-16 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-14 h-14 bg-violet-50 rounded-2xl flex items-center justify-center mb-4 text-violet-400">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-800 mb-1">No records found</p>
                            <p class="text-sm text-slate-500 mb-5">No executive orders match your current filters.</p>
                            @if(request()->anyFilled(['search', 'status', 'year']))
                                <a href="{{ route('executive-orders.index') }}" class="btn-secondary btn-sm">Clear Filters</a>
                            @else
                                <a href="{{ route('executive-orders.create') }}" class="btn-primary btn-sm">Upload New EO</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
    <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/50 flex items-center justify-between gap-4 flex-wrap">
        <p class="text-xs text-slate-400">
            Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ number_format($orders->total()) }}
        </p>
        {{ $orders->links() }}
    </div>
    @endif
</div>

@endsection
