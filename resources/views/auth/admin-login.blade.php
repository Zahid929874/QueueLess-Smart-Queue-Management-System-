@extends('layouts.app', ['title' => 'Admin Sign In'])

@section('content')
<div class="max-w-md mx-auto py-6 sm:py-10">
    <div class="bg-white dark:bg-[#0f172a] rounded-3xl p-8 sm:p-9 border border-rose-100 dark:border-rose-900/50 shadow-card space-y-6 interactive-card">
        <div class="text-center space-y-3">
            <a href="{{ route('home') }}" class="inline-block transition-transform hover:scale-105 active:scale-95" title="QueueLess">
                <img src="{{ asset('images/logo-light.png') }}" alt="QueueLess" class="dark:hidden h-12 sm:h-14 w-auto mx-auto object-contain" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
                <img src="{{ asset('images/logo-transparent.png') }}" alt="QueueLess" class="hidden dark:block h-12 sm:h-14 w-auto mx-auto object-contain" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
            </a>
            <span class="inline-flex px-3 py-1 rounded-full bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-[10px] font-bold font-mono">ADMINISTRATION</span>
            <p class="text-xs text-slate-500 dark:text-slate-400">Sign in to manage campus capacity, staff and operations.</p>
        </div>

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="login_role" value="ADMIN">
            <div>
                <label for="admin-email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Admin Email</label>
                <input id="admin-email" type="email" name="email" value="{{ old('email', 'admin@queueless.local') }}" required
                       class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500">
                @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="admin-password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Password</label>
                <input id="admin-password" type="password" name="password" value="password" required
                       class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500">
            </div>
            <label class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-rose-500 focus:ring-rose-500"> Remember me
            </label>
            <button type="submit" class="w-full py-3.5 rounded-full bg-slate-900 hover:bg-slate-800 text-white font-black text-sm shadow-md transition">Sign In as Admin</button>
        </form>

        <p class="text-center text-xs text-slate-500 dark:text-slate-400 pt-2">
            <a href="{{ route('admin.register') }}" class="font-bold text-rose-600 hover:underline">Have an invite? Register as Admin</a>
        </p>
    </div>
</div>
@endsection
