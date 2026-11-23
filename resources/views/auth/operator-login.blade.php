@extends('layouts.app', ['title' => 'Officer Sign In'])

@section('content')
<div class="max-w-md mx-auto py-6 sm:py-10">
    <div class="bg-white dark:bg-[#0f172a] rounded-3xl p-8 sm:p-9 border border-amber-100 dark:border-amber-900/50 shadow-card space-y-6 interactive-card">
        <div class="text-center space-y-3">
            <a href="{{ route('home') }}" class="inline-block transition-transform hover:scale-105 active:scale-95" title="QueueLess">
                <img src="{{ asset('images/logo-light.png') }}" alt="QueueLess" class="dark:hidden h-12 sm:h-14 w-auto mx-auto object-contain" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
                <img src="{{ asset('images/logo-transparent.png') }}" alt="QueueLess" class="hidden dark:block h-12 sm:h-14 w-auto mx-auto object-contain" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
            </a>
            <span class="inline-flex px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-[10px] font-bold font-mono">COUNTER OPERATOR</span>
            <p class="text-xs text-slate-500 dark:text-slate-400">Sign in to manage counters and service queues.</p>
        </div>

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="login_role" value="COUNTER_OPERATOR">
            <div>
                <label for="operator-email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Officer Email</label>
                <input id="operator-email" type="email" name="email" value="{{ old('email', 'acc.operator@queueless.local') }}" required
                       class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="operator-password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Access Passcode</label>
                <input id="operator-password" type="password" name="password" value="password" required
                       class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
            </div>
            <label class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-amber-500 focus:ring-amber-500"> Remember me
            </label>
            <button type="submit" class="w-full py-3.5 rounded-full bg-amber-500 hover:bg-amber-600 text-white font-black text-sm shadow-md shadow-amber-500/25 transition">Sign In as Operator</button>
        </form>

        <p class="text-center text-xs text-slate-500 dark:text-slate-400 pt-2">
            <a href="{{ route('operator.register') }}" class="font-bold text-amber-600 hover:underline">New officer? Request operator access</a>
        </p>
    </div>
</div>
@endsection
