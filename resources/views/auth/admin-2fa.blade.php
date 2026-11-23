@extends('layouts.app', ['title' => 'Admin 2FA Verification'])

@section('content')
<div class="max-w-md mx-auto py-10 sm:py-16">
    <div class="bg-white/95 dark:bg-[#0f172a]/95 rounded-3xl p-8 border border-rose-100 dark:border-rose-900/50 shadow-card space-y-5">
        <div class="text-center">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 border border-rose-200 dark:border-rose-900/60 flex items-center justify-center">
                <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
            <h1 class="mt-3 text-lg font-black text-slate-900 dark:text-white">Two-factor verification</h1>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Enter the six-digit authenticator code or a recovery code.</p>
        </div>

        <form method="POST" action="{{ route('admin.2fa.verify') }}" class="space-y-4">
            @csrf
            <div>
                <label for="two_factor_code" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">TOTP or Recovery Code</label>
                <input id="two_factor_code" name="two_factor_code" type="text" autocomplete="one-time-code" required
                       class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm font-mono text-center focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500">
                @error('two_factor_code') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="w-full py-3.5 rounded-full bg-slate-900 hover:bg-slate-800 text-white font-black text-sm">Verify & Sign In</button>
        </form>
    </div>
</div>
@endsection
