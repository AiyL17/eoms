<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — EOMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased min-h-screen flex">

    {{-- Left decorative panel --}}
    <div class="hidden lg:flex lg:w-1/2 flex-col justify-between p-12 relative overflow-hidden"
         style="background: linear-gradient(145deg, #3b1591 0%, #5b21b6 50%, #7c3aed 100%);">
        {{-- Background decorative circles --}}
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/5"></div>
        <div class="absolute top-1/3 -left-12 w-48 h-48 rounded-full bg-white/5"></div>
        <div class="absolute bottom-20 right-20 w-64 h-64 rounded-full bg-white/5"></div>

        <div class="relative z-10 flex items-center gap-3">
            <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                </svg>
            </div>
            <span class="text-white font-bold text-lg">EOMS</span>
        </div>

        <div class="relative z-10">
            <h2 class="text-white text-4xl font-bold leading-snug mb-4">Executive Order<br>Management System</h2>
            <p class="text-violet-200 text-base leading-relaxed max-w-sm">Digitally catalog, store, and track executive orders for the City Government — with a full audit trail.</p>

            <div class="mt-12 grid grid-cols-3 gap-4">
                <div class="bg-white/10 rounded-2xl p-4 text-center">
                    <p class="text-white text-2xl font-bold">PDF</p>
                    <p class="text-violet-300 text-xs mt-1">Secure Storage</p>
                </div>
                <div class="bg-white/10 rounded-2xl p-4 text-center">
                    <p class="text-white text-2xl font-bold">Audit</p>
                    <p class="text-violet-300 text-xs mt-1">Full Trail</p>
                </div>
                <div class="bg-white/10 rounded-2xl p-4 text-center">
                    <p class="text-white text-2xl font-bold">Role</p>
                    <p class="text-violet-300 text-xs mt-1">Based Access</p>
                </div>
            </div>
        </div>

        <p class="relative z-10 text-violet-300/70 text-xs">&copy; {{ date('Y') }} City Government — EOMS v1.0</p>
    </div>

    {{-- Right: Login form --}}
    <div class="flex-1 flex items-center justify-center bg-[#f5f4ff] p-8">
        <div class="w-full max-w-sm">

            {{-- Mobile logo --}}
            <div class="flex items-center gap-2 mb-8 lg:hidden">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: #5b21b6;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                    </svg>
                </div>
                <span class="font-bold text-slate-900">EOMS</span>
            </div>

            <h1 class="text-2xl font-bold text-slate-900 mb-1">Welcome back</h1>
            <p class="text-slate-500 text-sm mb-8">Sign in to your account to continue</p>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-7">
                <form action="{{ route('login') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="form-label">Email address</label>
                        <input
                            id="email" type="email" name="email"
                            value="{{ old('email') }}"
                            autocomplete="email" autofocus required
                            class="form-input {{ $errors->has('email') ? 'error' : '' }}"
                            placeholder="you@citygovernment.gov.ph"
                        >
                        @error('email')
                        <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="form-label">Password</label>
                        <input
                            id="password" type="password" name="password"
                            autocomplete="current-password" required
                            class="form-input {{ $errors->has('password') ? 'error' : '' }}"
                            placeholder="••••••••"
                        >
                        @error('password')
                        <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer select-none">
                            <input type="checkbox" name="remember"
                                   class="w-4 h-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                            Remember me
                        </label>
                    </div>

                    <button type="submit" class="w-full btn-primary justify-center py-3 text-sm mt-1">
                        Sign In
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                </form>
            </div>

            <p class="text-center text-slate-400 text-xs mt-6">&copy; {{ date('Y') }} City Government · EOMS v1.0</p>
        </div>
    </div>

</body>
</html>
