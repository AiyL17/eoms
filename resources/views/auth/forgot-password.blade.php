<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password — DTMS</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased min-h-screen flex">

    {{-- Left decorative panel --}}
    <div class="hidden lg:flex lg:w-1/2 flex-col justify-between p-12 relative overflow-hidden"
         style="background: linear-gradient(145deg, #3b1591 0%, #5b21b6 50%, #7c3aed 100%);">
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
            <h2 class="text-white text-4xl font-bold leading-snug mb-4">Password<br>Recovery</h2>
            <p class="text-violet-200 text-base leading-relaxed max-w-sm">Enter your registered email and we'll send you a secure link to reset your password.</p>
        </div>

        <p class="relative z-10 text-violet-300/70 text-xs">&copy; {{ date('Y') }} City Government — DTMS v1.0</p>
    </div>

    {{-- Right: form --}}
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

            <h1 class="text-2xl font-bold text-slate-900 mb-1">Forgot your password?</h1>
            <p class="text-slate-500 text-sm mb-8">No problem. Enter your email and we'll send a reset link.</p>

            {{-- Status message --}}
            @if(session('status'))
            <div class="flex items-start gap-3 px-4 py-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm mb-6">
                <svg class="w-5 h-5 shrink-0 mt-0.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p>{{ session('status') }}</p>
            </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-7">
                <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
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

                    <button type="submit" class="w-full btn-primary justify-center py-3 text-sm">
                        Send Reset Link
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                    </button>
                </form>
            </div>

            <p class="text-center mt-6">
                <a href="{{ route('login') }}" class="text-sm text-violet-600 hover:text-violet-800 font-semibold transition-colors">
                    ← Back to sign in
                </a>
            </p>

            <p class="text-center text-slate-400 text-xs mt-4">&copy; {{ date('Y') }} City Government · DTMS v1.0</p>
        </div>
    </div>

</body>
</html>
