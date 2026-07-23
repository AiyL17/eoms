<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — DTMS</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
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
            <span class="text-white font-bold text-lg">DTMS</span>
        </div>

        <div class="relative z-10">
            <h2 class="text-white text-4xl font-bold leading-snug mb-4">Document Tracking<br>Management System</h2>
            <p class="text-violet-200 text-base leading-relaxed max-w-sm">Digitally catalog, store, and track documents for the City Government — with a full audit trail.</p>

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

        <p class="relative z-10 text-violet-300/70 text-xs">&copy; {{ date('Y') }} City Government — DTMS v1.0</p>
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
                <span class="font-bold text-slate-900">DTMS</span>
            </div>

            <h1 class="text-2xl font-bold text-slate-900 mb-1">Welcome back</h1>
            <p class="text-slate-500 text-sm mb-8">Sign in to your account to continue</p>

            {{-- Maintenance notice --}}
            @if($maintenance)
            <div class="flex items-start gap-3 px-4 py-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm mb-6">
                <svg class="w-5 h-5 shrink-0 mt-0.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                </svg>
                <div>
                    <p class="font-semibold">System Under Maintenance</p>
                    <p class="text-xs text-amber-700 mt-0.5">The system is currently undergoing maintenance. Only administrators may log in at this time. Please check back later.</p>
                </div>
            </div>
            @endif

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
                        <div class="relative">
                            <input
                                id="password" type="password" name="password"
                                autocomplete="current-password" required
                                class="form-input pr-10 {{ $errors->has('password') ? 'error' : '' }}"
                                placeholder="••••••••"
                            >
                            <button type="button"
                                    onclick="togglePassword('password', this)"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 transition-colors"
                                    tabindex="-1" aria-label="Toggle password visibility">
                                <svg class="eye-on w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg class="eye-off w-4 h-4 hidden" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                        <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer select-none">
                            <input type="checkbox" name="remember"
                                   class="w-4 h-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                            Remember me
                        </label>
                        <a href="{{ route('password.request') }}"
                           class="text-sm font-semibold text-violet-600 hover:text-violet-800 transition-colors">
                            Forgot password?
                        </a>
                    </div>

                    <button type="submit" class="w-full btn-primary justify-center py-3 text-sm mt-1">
                        Sign In
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                </form>
            </div>

            <p class="text-center text-slate-400 text-xs mt-6">&copy; {{ date('Y') }} City Government · DTMS v1.0</p>


        </div>
    </div>

<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    btn.querySelector('.eye-on').classList.toggle('hidden', isHidden);
    btn.querySelector('.eye-off').classList.toggle('hidden', !isHidden);
}
</script>
</body>
</html>
