@extends('layouts.app')

@section('title', 'Edit User — ' . $user->name)
@section('page-title', 'Edit User')

@section('breadcrumb')
    <a href="{{ route('admin.users.index') }}" class="hover:text-violet-600 transition-colors">User Profiles</a>
    <span class="mx-1 opacity-40">/</span>
    <span class="text-slate-700 font-semibold">{{ $user->name }}</span>
@endsection

@section('content')
<form action="{{ route('admin.users.update', $user) }}" method="POST" id="user-form">
@csrf
@method('PUT')

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ── LEFT: Form ───────────────────────────────────────────────────── --}}
    <div class="xl:col-span-2 space-y-5">

        {{-- 1. Profile --}}
        <div class="card">
            <div class="p-6">
                <h3 class="form-section-title">1 — Profile</h3>
                <div class="space-y-5">
                    <div>
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                               class="form-input {{ $errors->has('name') ? 'error' : '' }}" required>
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                               class="form-input {{ $errors->has('email') ? 'error' : '' }}" required>
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="role" class="form-label">System Role</label>
                            <select name="role" id="role"
                                    class="form-input {{ $errors->has('role') ? 'error' : '' }}"
                                    required {{ auth()->id() === $user->id ? 'disabled' : '' }}>
                                <option value="staff" {{ old('role', $user->role) === 'staff' ? 'selected' : '' }}>Staff</option>
                                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrator</option>
                            </select>
                            @if(auth()->id() === $user->id)
                                <input type="hidden" name="role" value="{{ $user->role }}">
                                <p class="form-hint text-amber-600 font-medium mt-1.5">You cannot change your own role.</p>
                            @endif
                            @error('role') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="position" class="form-label">
                                Position / Title
                                <span class="text-slate-400 font-normal ml-1">(optional)</span>
                            </label>
                            <input type="text" name="position" id="position"
                                   value="{{ old('position', $user->position) }}"
                                   class="form-input {{ $errors->has('position') ? 'error' : '' }}">
                            @error('position') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Change Password --}}
        <div class="card">
            <div class="p-6">
                <h3 class="form-section-title">2 — Change Password</h3>
                <div class="space-y-5">
                    <div>
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" name="password" id="password"
                               class="form-input {{ $errors->has('password') ? 'error' : '' }}"
                               placeholder="Min. 8 characters">
                        @error('password') <p class="form-error">{{ $message }}</p> @enderror
                        <p class="form-hint mt-2 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                            Leave blank to keep the current password unchanged.
                        </p>
                    </div>
                    <div>
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-input" placeholder="Repeat new password">
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3 pb-2">
            <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" id="submit-btn" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                <span id="submit-label">Save Changes</span>
            </button>
        </div>
    </div>

    {{-- ── RIGHT: User info panel ───────────────────────────────────────── --}}
    <div class="sticky top-20 self-start space-y-5">

        {{-- Live preview --}}
        <div class="card">
            <div class="p-5 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Preview</h3>
                <p class="text-xs text-slate-400 mt-0.5">Updates as you edit</p>
            </div>
            <div class="p-5 space-y-4">
                <div class="flex items-center gap-3">
                    <div id="prev-avatar" class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-bold shrink-0"
                         style="background: linear-gradient(135deg, #6d28d9, #7c3aed);">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div>
                        <p id="prev-name" class="text-sm font-bold text-slate-800">{{ $user->name }}</p>
                        <p id="prev-email" class="text-xs text-slate-400">{{ $user->email }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <span class="text-xs text-slate-400 font-medium">Role</span>
                    <span id="prev-role" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $user->role === 'admin' ? 'bg-violet-50 text-violet-700 border border-violet-100' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $user->role === 'admin' ? 'bg-violet-500' : 'bg-slate-400' }}"></span>
                        {{ $user->role === 'admin' ? 'Administrator' : 'Staff' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-400 font-medium">Position</span>
                    <span id="prev-position" class="text-xs font-semibold text-slate-700">{{ $user->position ?: '—' }}</span>
                </div>
            </div>
        </div>

        {{-- Account info --}}
        <div class="bg-slate-50 rounded-2xl border border-slate-100 p-4 space-y-2.5 text-xs">
            <p class="font-bold text-slate-700 uppercase tracking-widest text-[10px]">Account Info</p>
            <div class="flex items-center justify-between">
                <span class="text-slate-400 font-medium">Member since</span>
                <span class="font-semibold text-slate-700">{{ $user->created_at->format('M d, Y') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-400 font-medium">EOs uploaded</span>
                <span class="font-semibold text-slate-700">{{ $user->uploadedOrders()->count() }}</span>
            </div>
            @if(auth()->id() === $user->id)
            <div class="flex items-center gap-2 mt-2 pt-2 border-t border-slate-200">
                <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                <span class="text-amber-700 font-medium">This is your own account.</span>
            </div>
            @endif
        </div>
    </div>

</div>
</form>

@push('scripts')
<script>
(function () {
    const nameInput  = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const roleSelect = document.getElementById('role');
    const posInput   = document.getElementById('position');
    const submitBtn  = document.getElementById('submit-btn');
    const submitLbl  = document.getElementById('submit-label');

    function updatePreview() {
        const name = nameInput.value.trim();
        const initials = name ? name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase() : '??';
        document.getElementById('prev-avatar').textContent = initials;
        document.getElementById('prev-name').textContent    = name || '—';
        document.getElementById('prev-email').textContent   = emailInput.value || '—';
        document.getElementById('prev-position').textContent = posInput.value || '—';

        if (!roleSelect.disabled) {
            const isAdmin = roleSelect.value === 'admin';
            document.getElementById('prev-role').innerHTML = isAdmin
                ? `<span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span>Administrator`
                : `<span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Staff`;
            document.getElementById('prev-role').className = isAdmin
                ? 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-violet-50 text-violet-700 border border-violet-100'
                : 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200';
        }
    }

    nameInput.addEventListener('input',   updatePreview);
    emailInput.addEventListener('input',  updatePreview);
    posInput.addEventListener('input',    updatePreview);
    if (!roleSelect.disabled) roleSelect.addEventListener('change', updatePreview);

    document.getElementById('user-form').addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitLbl.textContent = 'Saving…';
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
    });
})();
</script>
@endpush
@endsection
