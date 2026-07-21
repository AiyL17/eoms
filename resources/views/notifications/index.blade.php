@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('header-actions')
    @if(auth()->user()->unreadNotifications->count() > 0)
    <form action="{{ route('notifications.read-all') }}" method="POST" data-no-loader>
        @csrf
        <button type="submit"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold
                       bg-violet-50 text-violet-700 border border-violet-200
                       hover:bg-violet-100 hover:border-violet-300 transition-all duration-150">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
            Mark all as read
        </button>
    </form>
    @endif
@endsection

@section('content')

@php
    $unreadCount = auth()->user()->unreadNotifications->count();
@endphp

<div class="flex gap-6 items-start">

    {{-- ── Main notifications column ── --}}
    <div class="flex-1 min-w-0">

    {{-- ── Summary bar ── --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <p class="text-sm font-semibold text-slate-700">
                {{ $notifications->total() }} notification{{ $notifications->total() !== 1 ? 's' : '' }}
            </p>
            @if($unreadCount > 0)
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold
                         bg-violet-100 text-violet-700 border border-violet-200">
                {{ $unreadCount }} unread
            </span>
            @endif
        </div>
        @if(!$notifications->isEmpty())
        <p class="text-xs text-slate-400">Click a notification to mark it as read</p>
        @endif
    </div>

    {{-- ── Empty state ── --}}
    @if($notifications->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-20 text-center">
        <div class="w-14 h-14 bg-violet-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-violet-400">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
        </div>
        <p class="text-sm font-bold text-slate-700">All caught up</p>
        <p class="text-xs text-slate-400 mt-1">No notifications to show right now.</p>
    </div>

    @else

    {{-- ── Notification list ── --}}
    <div class="space-y-2">
        @foreach($notifications as $n)
        @php
            $data   = $n->data;
            $isRead = (bool) $n->read_at;

            $icon = match($data['type'] ?? '') {
                'doc_uploaded'           => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'ring' => 'ring-emerald-200',
                                             'path' => 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5'],
                'doc_updated'            => ['bg' => 'bg-blue-100',    'text' => 'text-blue-600',    'ring' => 'ring-blue-200',
                                             'path' => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z'],
                'doc_type_changed'       => ['bg' => 'bg-sky-100',     'text' => 'text-sky-600',     'ring' => 'ring-sky-200',
                                             'path' => 'M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5'],
                'doc_archived'           => ['bg' => 'bg-orange-100',  'text' => 'text-orange-600',  'ring' => 'ring-orange-200',
                                             'path' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.375c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.375c0 .621.504 1.125 1.125 1.125z'],
                'doc_expiring'           => ['bg' => 'bg-amber-100',   'text' => 'text-amber-600',   'ring' => 'ring-amber-200',
                                             'path' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'],
                'doc_expiration_warning' => ['bg' => 'bg-red-100',     'text' => 'text-red-600',     'ring' => 'ring-red-200',
                                             'path' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'],
                default                  => ['bg' => 'bg-slate-100',   'text' => 'text-slate-500',   'ring' => 'ring-slate-200',
                                             'path' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0'],
            };
        @endphp

        <form action="{{ route('notifications.read', $n->id) }}" method="POST" data-no-loader>
            @csrf
            <button type="submit" class="w-full text-left group">
                <div class="relative flex items-start gap-4 px-5 py-4 rounded-2xl border transition-all duration-200
                            {{ $isRead
                                ? 'bg-white border-slate-100 shadow-sm hover:border-slate-200 hover:shadow'
                                : 'bg-white border-violet-100 shadow-sm ring-1 ring-violet-50 hover:border-violet-300 hover:shadow-md' }}">

                    {{-- Unread left accent bar --}}
                    @unless($isRead)
                    <span class="absolute left-0 inset-y-3 w-1 rounded-r-full bg-violet-500"></span>
                    @endunless

                    {{-- Icon --}}
                    <div class="shrink-0 mt-0.5">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center
                                    {{ $icon['bg'] }} {{ $icon['text'] }}
                                    {{ $isRead ? '' : 'ring-2 ring-offset-1 ' . $icon['ring'] }}">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon['path'] }}" />
                            </svg>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm leading-snug
                                  {{ $isRead ? 'text-slate-500' : 'font-semibold text-slate-800' }}">
                            {{ $data['message'] ?? 'Notification' }}
                        </p>

                        @if(!empty($data['title']))
                        <p class="mt-0.5 text-xs {{ $isRead ? 'text-slate-400' : 'text-slate-500 font-medium' }} truncate">
                            {{ $data['title'] }}
                        </p>
                        @endif

                        <div class="mt-1.5 flex items-center gap-1.5 text-[11px] text-slate-400">
                            <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ $n->created_at->diffForHumans() }}</span>
                            <span class="text-slate-300">&bull;</span>
                            <span>{{ $n->created_at->format('M d, Y') }}</span>
                            <span class="text-slate-300">&bull;</span>
                            <span>{{ $n->created_at->format('h:i A') }}</span>
                        </div>
                    </div>

                    {{-- Unread dot (centered) --}}
                    @if(!$isRead)
                    <span class="w-2.5 h-2.5 bg-violet-500 rounded-full shrink-0 self-center"></span>
                    @endif

                </div>
            </button>
        </form>
        @endforeach
    </div>

    {{-- ── Pagination ── --}}
    @if($notifications->hasPages())
    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
    @endif

    @endif

    </div>{{-- end main column --}}

    {{-- ── Sidebar panel ── --}}
    <div class="w-64 shrink-0 hidden lg:flex flex-col gap-4">

        {{-- Stats card --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Overview</p>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                        Unread
                    </div>
                    <span class="text-sm font-bold text-slate-800">{{ $unreadCount }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                        Total
                    </div>
                    <span class="text-sm font-bold text-slate-800">{{ $notifications->total() }}</span>
                </div>
            </div>
        </div>

        {{-- Tips card --}}
        <div class="bg-violet-50 border border-violet-100 rounded-2xl p-5">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-4 h-4 text-violet-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <p class="text-xs font-bold text-violet-700">Tip</p>
            </div>
            <p class="text-xs text-violet-600 leading-relaxed">
                Clicking a notification marks it as read and records the action.
            </p>
        </div>

    </div>{{-- end sidebar --}}

</div>{{-- end flex wrapper --}}
@endsection
