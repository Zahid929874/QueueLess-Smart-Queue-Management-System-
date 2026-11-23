@extends('layouts.app', ['title' => 'Sign In'])

@section('content')
<div class="max-w-md mx-auto py-6 sm:py-10">

    <div class="bg-white dark:bg-[#0f172a] rounded-3xl p-8 sm:p-9 border border-slate-100 dark:border-slate-800 shadow-card space-y-6 interactive-card">

        <div class="text-center space-y-3">
            <a href="{{ route('home') }}" class="inline-block transition-transform hover:scale-105 active:scale-95" title="QueueLess">
                <img src="{{ asset('images/logo-light.png') }}" alt="QueueLess" class="dark:hidden h-12 sm:h-14 w-auto mx-auto object-contain drop-shadow-xs" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
                <img src="{{ asset('images/logo-transparent.png') }}" alt="QueueLess" class="hidden dark:block h-12 sm:h-14 w-auto mx-auto object-contain drop-shadow-xs" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
            </a>
            <p class="text-xs text-slate-500 dark:text-slate-400">Access your active service queue passes, counters, and admin stations</p>
        </div>

        <!-- 1-Click Role Profiles for Evaluation -->
        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-[#142034] border border-slate-100 dark:border-slate-700/70 space-y-2.5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#00c389] block text-center">Fast-Login Demo Personas</span>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <a href="{{ route('demo.login', 'student') }}" class="p-2.5 rounded-xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-700 hover:border-[#00c389] dark:hover:border-[#00c389] hover:bg-[#00c389]/10 hover:text-[#00c389] transition text-center font-bold text-slate-700 dark:text-slate-200 shadow-2xs">
                    👤 Student (Tanvir)
                </a>
                <a href="{{ route('demo.login', 'student2') }}" class="p-2.5 rounded-xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-700 hover:border-[#00c389] dark:hover:border-[#00c389] hover:bg-[#00c389]/10 hover:text-[#00c389] transition text-center font-bold text-slate-700 dark:text-slate-200 shadow-2xs">
                    👤 Student (Sadia)
                </a>
                <a href="{{ route('demo.login', 'operator') }}" class="p-2.5 rounded-xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-700 hover:border-teal-400 dark:hover:border-teal-500 hover:bg-teal-50 dark:hover:bg-teal-950/40 hover:text-teal-700 dark:hover:text-teal-300 transition text-center font-bold text-slate-700 dark:text-slate-200 shadow-2xs">
                    🎯 Accounts Desk
                </a>
                <a href="{{ route('demo.login', 'admin') }}" class="p-2.5 rounded-xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-700 hover:border-amber-400 dark:hover:border-amber-500 hover:bg-amber-50 dark:hover:bg-amber-950/40 hover:text-amber-800 dark:hover:text-amber-300 transition text-center font-bold text-slate-700 dark:text-slate-200 shadow-2xs">
                    ⚡ Admin Station
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Email Address</label>
                <input id="email" type="email" name="email" value="{{ old('email', 'student1@queueless.local') }}" required
                       class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] placeholder-slate-400 transition">
                @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Password</label>
                <input id="password" type="password" name="password" value="password" required
                       class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] placeholder-slate-400 transition">
                @error('password') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 text-slate-500 dark:text-slate-400 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-[#00c389] focus:ring-[#00c389]">
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-full bg-[#00c389] hover:bg-[#00ab78] text-white font-black text-sm tracking-wide shadow-md shadow-emerald-500/25 hover:shadow-emerald-500/40 transition hover:scale-[1.02] active:scale-95">
                Sign In
            </button>
        </form>

        <p class="text-center text-xs text-slate-500 dark:text-slate-400 pt-2">
            Don't have an account yet?
            @if($isOperatorLogin)
                <a href="{{ route('operator.register') }}" class="font-bold text-amber-600 hover:underline">New officer? Request operator access</a>
            @else
                <a href="{{ route('register') }}" class="font-bold text-[#00c389] hover:underline">New student? Register</a>
            @endif
        </p>
    </div>

</div>
@endsection
