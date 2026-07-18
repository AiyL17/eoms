<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password — DTMS</title>
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
            <h2 class="text-white text-4xl font-bold leading-snug mb-4">Set a New<br>Password</h2>
            <p class="text-violet-200 text-base leading-relaxed max-w-sm">Choose a strong password — at least 8 characters, with mixed case, numbers, and symbols.</p>
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

            <h1 class="text-2xl font-bold text-slate-900 mb-1">Set a new password</h1>
            <p class="text-slate-500 text-sm mb-8">Enter and confirm your new password below.</p>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-7">
                <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div>
                        <label for="email" class="form-label">Email address</label>
                        <input
                            id="email" type="email" name="email"
                            value="{{ old('email', $email) }}"
                            autocomplete="email" required
                            class="form-input {{ $errors->has('email') ? 'error' : '' }}"
                        >
                        @error('email')
                        <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="form-label">New Password</label>
                        <div class="relative">
                            <input
                                id="password" type="password" name="password"
                                autocomplete="new-password" required autofocus
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
                        <p class="form-hint mt-1.5">Min 8 characters, mixed case, number, and symbol.</p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <div class="relative">
                            <input
                                id="password_confirmation" type="password" name="password_confirmation"
                                autocomplete="new-password" required
                                class="form-input pr-10"
                                placeholder="••••••••"
                            >
                            <button type="button"
                                    onclick="togglePassword('password_confirmation', this)"
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
                    </div>

                    <button type="submit" class="w-full btn-primary justify-center py-3 text-sm mt-1">
                        Reset Password
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
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
