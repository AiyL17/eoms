@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('breadcrumb')
    <span class="text-slate-700 font-semibold">Edit Profile</span>
@endsection

@section('content')
@php
    // Decide which tab to re-open after a failed validation
    $activeTab = $errors->updatePassword->any() ? 'password' : 'info';
@endphp

<div class="max-w-5xl mx-auto" x-data="{ tab: '{{ $activeTab }}' }">

    {{-- ── Profile Header ────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-r from-violet-600 to-indigo-600 rounded-2xl px-6 py-6 mb-6 flex items-center gap-5">

        {{-- Avatar — click to open modal --}}
        <div class="relative shrink-0 group" data-tour="profile-avatar">
            <button type="button" id="open-avatar-modal"
                    class="w-16 h-16 rounded-2xl overflow-hidden bg-white/20 flex items-center justify-center
                           text-white text-2xl font-bold select-none focus:outline-none"
                    title="Change profile picture">
                @if($user->avatar)
                    <img id="avatar-header-img"
                         src="{{ asset('storage/' . $user->avatar) }}"
                         alt="{{ $user->name }}"
                         class="w-full h-full object-cover">
                @else
                    <span id="avatar-header-initials">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                @endif
            </button>
            {{-- Camera badge --}}
            <div class="absolute -bottom-1.5 -right-1.5 w-6 h-6 rounded-full bg-white shadow
                        flex items-center justify-center pointer-events-none">
                <svg class="w-3.5 h-3.5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                </svg>
            </div>
        </div>

        <div class="flex-1 min-w-0">
            <h2 class="text-white text-xl font-bold truncate">{{ $user->name }}</h2>
            <p class="text-white/70 text-sm mt-0.5">{{ $user->position ?? ucfirst($user->role) }}</p>
        </div>
        <span class="shrink-0 inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold bg-white/20 text-white/90 uppercase tracking-wide">
            {{ ucfirst($user->role) }}
        </span>
    </div>

    {{-- ── Two-Column Layout ───────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── LEFT: Account Details (always visible) ──────────────────────── --}}
        <div class="space-y-5">

            {{-- Account Details card --}}
            <div class="card">
                <div class="p-5 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800">Account Details</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Managed by your administrator.</p>
                </div>
                <div class="p-5 space-y-4">

                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Role</p>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-violet-100 text-violet-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-800">{{ ucfirst($user->role) }}</p>
                        </div>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Member Since</p>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-800">{{ $user->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Total Uploads</p>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-800">{{ $user->uploadedOrders()->count() }} EOs</p>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── E-Signature card ──────────────────────────────────────── --}}
            <div class="card" data-tour="profile-signature">
                <div class="p-5 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800">E-Signature</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Used when signing executive orders.</p>
                </div>
                <div class="p-5 space-y-4">

                    {{-- Preview of saved signature, or placeholder --}}
                    <div class="rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden" style="height: 90px;">
                        @if($user->signature_data)
                            <img id="sig-preview-img" src="{{ $user->signature_data }}" alt="Your e-signature"
                                 class="max-h-full max-w-full object-contain object-center p-2">
                        @else
                            <div id="sig-preview-empty" class="flex flex-col items-center gap-1 text-slate-300">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                </svg>
                                <span class="text-xs font-medium">No signature yet</span>
                            </div>
                            <img id="sig-preview-img" src="" alt="Your e-signature"
                                 class="hidden max-h-full max-w-full object-contain object-center p-2">
                        @endif
                    </div>

                    {{-- Action buttons --}}
                    <div class="flex gap-2">
                        <button type="button" id="open-sig-modal"
                                class="btn-primary flex-1 justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                            </svg>
                            {{ $user->signature_data ? 'Update Signature' : 'Add Signature' }}
                        </button>
                        @if($user->signature_data)
                        <form action="{{ route('profile.update-signature') }}" method="POST" id="clear-sig-form">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="signature_data" value="">
                            <button type="submit"
                                    class="btn-secondary px-3"
                                    title="Remove signature"
                                    data-confirm="Remove your saved signature? This cannot be undone.">
                                <svg class="w-4 h-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </form>
                        @endif
                    </div>

                </div>
            </div>

        </div>

        {{-- ── RIGHT: Tab Panels ────────────────────────────────────────────── --}}
        <div class="lg:col-span-2">
            <div id="profile-card" class="card overflow-hidden" data-tour="profile-info">

                {{-- Tab Nav --}}
                <div class="flex border-b border-slate-100 bg-slate-50/60">
                    <button type="button"
                            id="tab-btn-info"
                            @click="tab = 'info'"
                            :class="tab === 'info'
                                ? 'border-b-2 border-violet-600 text-violet-700 bg-white'
                                : 'text-slate-500 hover:text-slate-700 hover:bg-white/60'"
                            class="flex items-center gap-2 px-6 py-3.5 text-sm font-semibold transition-all -mb-px">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        Personal Information
                        @if($errors->updateInfo->any())
                            <span class="w-2 h-2 rounded-full bg-red-500 shrink-0"></span>
                        @endif
                    </button>
                    <button type="button"
                            id="tab-btn-password"
                            @click="tab = 'password'"
                            :class="tab === 'password'
                                ? 'border-b-2 border-violet-600 text-violet-700 bg-white'
                                : 'text-slate-500 hover:text-slate-700 hover:bg-white/60'"
                            class="flex items-center gap-2 px-6 py-3.5 text-sm font-semibold transition-all -mb-px">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        Password & Security
                        @if($errors->updatePassword->any())
                            <span class="w-2 h-2 rounded-full bg-red-500 shrink-0"></span>
                        @endif
                    </button>
                </div>

                {{-- ── Tab: Personal Info ──────────────────────────────────── --}}
                <div x-show="tab === 'info'" x-cloak>
                    <div class="p-6 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-slate-800">Personal Information</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Update your name, email address, and position.</p>
                    </div>
                    <form action="{{ route('profile.update-info') }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="p-6 space-y-5">

                            <div>
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" name="name" id="name"
                                       value="{{ old('name', $user->name) }}"
                                       class="form-input {{ $errors->updateInfo->has('name') ? 'error' : '' }}"
                                       required>
                                @if($errors->updateInfo->has('name'))
                                    <p class="form-error">{{ $errors->updateInfo->first('name') }}</p>
                                @endif
                            </div>

                            <div>
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" name="email" id="email"
                                       value="{{ old('email', $user->email) }}"
                                       class="form-input {{ $errors->updateInfo->has('email') ? 'error' : '' }}"
                                       required>
                                @if($errors->updateInfo->has('email'))
                                    <p class="form-error">{{ $errors->updateInfo->first('email') }}</p>
                                @endif
                            </div>

                            <div>
                                <label for="position" class="form-label">
                                    Position / Title
                                    <span class="text-slate-400 font-normal ml-1">(optional)</span>
                                </label>
                                <input type="text" name="position" id="position"
                                       value="{{ old('position', $user->position) }}"
                                       class="form-input {{ $errors->updateInfo->has('position') ? 'error' : '' }}"
                                       placeholder="e.g. Records Officer, City Administrator">
                                @if($errors->updateInfo->has('position'))
                                    <p class="form-error">{{ $errors->updateInfo->first('position') }}</p>
                                @endif
                            </div>

                            <div class="pt-2 flex justify-end">
                                <button type="submit" class="btn-primary">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                    </svg>
                                    Save Changes
                                </button>
                            </div>

                        </div>
                    </form>
                </div>

                {{-- ── Tab: Password ────────────────────────────────────────── --}}
                <div x-show="tab === 'password'" x-cloak data-tour="profile-password">
                    <div class="p-6 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-slate-800">Password & Security</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Use a strong password with at least 8 characters, including letters and numbers.</p>
                    </div>
                    <form action="{{ route('profile.update-password') }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="p-6 space-y-5">

                            <div>
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" name="current_password" id="current_password"
                                       class="form-input {{ $errors->updatePassword->has('current_password') ? 'error' : '' }}"
                                       autocomplete="current-password">
                                @if($errors->updatePassword->has('current_password'))
                                    <p class="form-error">{{ $errors->updatePassword->first('current_password') }}</p>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" name="password" id="password"
                                           class="form-input {{ $errors->updatePassword->has('password') ? 'error' : '' }}"
                                           autocomplete="new-password">
                                    @if($errors->updatePassword->has('password'))
                                        <p class="form-error">{{ $errors->updatePassword->first('password') }}</p>
                                    @endif
                                </div>
                                <div>
                                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                           class="form-input"
                                           autocomplete="new-password">
                                </div>
                            </div>

                            <p class="form-hint flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                </svg>
                                Minimum 8 characters — must include letters and numbers.
                            </p>

                            <div class="pt-2 flex justify-end">
                                <button type="submit" class="btn-primary">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                    </svg>
                                    Update Password
                                </button>
                            </div>

                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div>{{-- end two-column --}}
</div>

{{-- ══════════════════════════════════════ AVATAR MODAL ══ --}}
<div id="avatar-modal"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden"
     role="dialog" aria-modal="true" aria-labelledby="avatar-modal-title">

    {{-- Backdrop --}}
    <div id="avatar-backdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

    {{-- Panel --}}
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm flex flex-col overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h3 id="avatar-modal-title" class="text-sm font-bold text-slate-800">Profile Picture</h3>
                <p class="text-xs text-slate-400 mt-0.5">JPG, PNG, GIF or WebP — max 2 MB.</p>
            </div>
            <button type="button" id="close-avatar-modal"
                    class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="p-6 space-y-5">

            {{-- Preview --}}
            <div class="flex justify-center">
                <div id="avatar-preview-wrap"
                     class="w-28 h-28 rounded-2xl overflow-hidden bg-slate-100 border-2 border-dashed border-slate-200
                            flex items-center justify-center text-slate-800 text-3xl font-bold select-none">
                    @if($user->avatar)
                        <img id="avatar-preview-img"
                             src="{{ asset('storage/' . $user->avatar) }}"
                             alt="Preview"
                             class="w-full h-full object-cover">
                    @else
                        <span id="avatar-preview-initials">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        <img id="avatar-preview-img" src="" alt="Preview" class="hidden w-full h-full object-cover">
                    @endif
                </div>
            </div>

            {{-- File picker --}}
            <form id="avatar-form" action="{{ route('profile.update-avatar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" id="avatar-file-input" name="avatar"
                       accept="image/jpeg,image/png,image/gif,image/webp"
                       class="hidden">
                <label for="avatar-file-input"
                       class="btn-secondary w-full justify-center cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    <span id="avatar-file-label">Choose a photo</span>
                </label>
                <p id="avatar-hint" class="form-hint text-center mt-2">No file chosen.</p>
            </form>

        </div>

        {{-- Footer --}}
        <div class="flex items-center gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50/60">
            {{-- Remove photo (left side, only when one exists) --}}
            @if($user->avatar)
            <form action="{{ route('profile.remove-avatar') }}" method="POST" id="remove-avatar-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger"
                        data-confirm="Remove your profile picture? This cannot be undone.">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                    Remove
                </button>
            </form>
            @endif
            <div class="flex-1"></div>
            <button type="button" id="cancel-avatar-modal" class="btn-secondary">Cancel</button>
            <button type="button" id="save-avatar-btn" class="btn-primary" disabled>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Save Photo
            </button>
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════ E-SIGNATURE MODAL ══ --}}
<div id="sig-modal"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden"
     role="dialog" aria-modal="true" aria-labelledby="sig-modal-title">

    {{-- Backdrop --}}
    <div id="sig-backdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

    {{-- Panel --}}
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg flex flex-col overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h3 id="sig-modal-title" class="text-sm font-bold text-slate-800">Draw Your E-Signature</h3>
                <p class="text-xs text-slate-400 mt-0.5">Use mouse or touch to sign in the box below.</p>
            </div>
            <button type="button" id="close-sig-modal"
                    class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Canvas --}}
        <div class="p-6">
            <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-white overflow-hidden">
                <canvas id="signature-pad"
                        class="w-full touch-none block"
                        style="height: 200px; cursor: crosshair;"></canvas>
            </div>
            <div class="flex items-center justify-between mt-3">
                <p class="form-hint flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    Draw the signatory's signature above.
                </p>
                <button type="button" id="clear-sig-canvas"
                        class="text-xs font-semibold text-slate-400 hover:text-red-500 transition-colors flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Clear
                </button>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50/60">
            <button type="button" id="cancel-sig-modal" class="btn-secondary">Cancel</button>
            <form action="{{ route('profile.update-signature') }}" method="POST" id="signature-form">
                @csrf
                @method('PATCH')
                <input type="hidden" name="signature_data" id="signature-data" value="">
                <button type="submit" id="save-sig-btn" class="btn-primary" disabled>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                    </svg>
                    Save Signature
                </button>
            </form>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Avatar Modal ──────────────────────────────────────────────────────────────
(function () {
    const modal       = document.getElementById('avatar-modal');
    const backdrop    = document.getElementById('avatar-backdrop');
    const openBtn     = document.getElementById('open-avatar-modal');
    const closeBtn    = document.getElementById('close-avatar-modal');
    const cancelBtn   = document.getElementById('cancel-avatar-modal');
    const saveBtn     = document.getElementById('save-avatar-btn');
    const fileInput   = document.getElementById('avatar-file-input');
    const form        = document.getElementById('avatar-form');
    const previewImg  = document.getElementById('avatar-preview-img');
    const previewInit = document.getElementById('avatar-preview-initials');
    const hintEl      = document.getElementById('avatar-hint');
    const fileLbl     = document.getElementById('avatar-file-label');

    // Also update the header avatar on save for instant feedback
    const headerImg   = document.getElementById('avatar-header-img');
    const headerInit  = document.getElementById('avatar-header-initials');

    function openModal() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        // Reset picker state
        fileInput.value = '';
        saveBtn.disabled = true;
        fileLbl.textContent = 'Choose a photo';
        hintEl.textContent  = 'No file chosen.';
        hintEl.classList.remove('text-red-500');
        // Revert preview to saved state if user cancels
        @if($user->avatar)
            previewImg.src = '{{ asset('storage/' . $user->avatar) }}';
            previewImg.classList.remove('hidden');
            if (previewInit) previewInit.classList.add('hidden');
        @else
            previewImg.src = '';
            previewImg.classList.add('hidden');
            if (previewInit) previewInit.classList.remove('hidden');
        @endif
    }

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

    // File chosen — show preview
    fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];
        if (!file) return;

        // Validate size (2 MB)
        if (file.size > 2 * 1024 * 1024) {
            hintEl.textContent = 'File is too large. Maximum size is 2 MB.';
            hintEl.classList.add('text-red-500');
            fileInput.value  = '';
            saveBtn.disabled = true;
            return;
        }

        hintEl.classList.remove('text-red-500');
        fileLbl.textContent = file.name;
        hintEl.textContent  = (file.size / 1024).toFixed(1) + ' KB';
        saveBtn.disabled    = false;

        const reader = new FileReader();
        reader.onload = (e) => {
            previewImg.src = e.target.result;
            previewImg.classList.remove('hidden');
            if (previewInit) previewInit.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    });

    // Save — submit the form
    saveBtn.addEventListener('click', () => {
        if (fileInput.files.length === 0) return;
        form.submit();
    });
})();
</script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
(function () {
    const modal      = document.getElementById('sig-modal');
    const backdrop   = document.getElementById('sig-backdrop');
    const openBtn    = document.getElementById('open-sig-modal');
    const closeBtn   = document.getElementById('close-sig-modal');
    const cancelBtn  = document.getElementById('cancel-sig-modal');
    const clearBtn   = document.getElementById('clear-sig-canvas');
    const saveBtn    = document.getElementById('save-sig-btn');
    const canvas     = document.getElementById('signature-pad');
    const sigData    = document.getElementById('signature-data');
    const previewImg = document.getElementById('sig-preview-img');
    const emptyEl    = document.getElementById('sig-preview-empty');

    // ── Signature Pad ──────────────────────────────────────────────────────
    const signaturePad = new SignaturePad(canvas, {
        minWidth: 0.8,
        maxWidth: 2.5,
        penColor: '#1e293b',
        backgroundColor: 'rgba(0,0,0,0)',
    });

    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width  = canvas.offsetWidth  * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        signaturePad.clear();
        saveBtn.disabled = true;
    }

    // Sync save button state
    signaturePad.addEventListener('endStroke', () => {
        saveBtn.disabled = signaturePad.isEmpty();
    });

    // ── Modal open / close ─────────────────────────────────────────────────
    function openModal() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        // Resize canvas after it becomes visible
        requestAnimationFrame(() => resizeCanvas());
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        signaturePad.clear();
        saveBtn.disabled = true;
        sigData.value = '';
    }

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);

    // Close on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

    // ── Clear canvas ───────────────────────────────────────────────────────
    clearBtn.addEventListener('click', () => {
        signaturePad.clear();
        saveBtn.disabled = true;
        sigData.value = '';
    });

    // ── On submit: capture data URL + update preview ───────────────────────
    document.getElementById('signature-form').addEventListener('submit', (e) => {
        if (signaturePad.isEmpty()) {
            e.preventDefault();
            return;
        }
        const dataUrl = signaturePad.toDataURL('image/png');
        sigData.value = dataUrl;

        // Update inline preview immediately
        if (previewImg) {
            previewImg.src = dataUrl;
            previewImg.classList.remove('hidden');
        }
        if (emptyEl) emptyEl.classList.add('hidden');
    });

    window.addEventListener('resize', () => {
        if (!modal.classList.contains('hidden')) resizeCanvas();
    });
})();
</script>
@endpush
