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
        <div class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center shrink-0 text-white text-2xl font-bold select-none">
            {{ strtoupper(substr($user->name, 0, 2)) }}
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

        </div>

        {{-- ── RIGHT: Tab Panels ────────────────────────────────────────────── --}}
        <div class="lg:col-span-2">
            <div class="card overflow-hidden">

                {{-- Tab Nav --}}
                <div class="flex border-b border-slate-100 bg-slate-50/60">
                    <button type="button"
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
                <div x-show="tab === 'password'" x-cloak>
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
@endsection
