@extends('layouts.app', ['title' => 'Student Sign In'])

@section('content')
<div class="max-w-md mx-auto py-6 sm:py-10">
    <div class="bg-white dark:bg-[#0f172a] rounded-3xl p-8 sm:p-9 border border-slate-100 dark:border-slate-800 shadow-card space-y-6 interactive-card">
        <div class="text-center space-y-3">
            <a href="{{ route('home') }}" class="inline-block transition-transform hover:scale-105 active:scale-95" title="QueueLess">
                <img src="{{ asset('images/logo-light.png') }}" alt="QueueLess" class="dark:hidden h-12 sm:h-14 w-auto mx-auto object-contain" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
                <img src="{{ asset('images/logo-transparent.png') }}" alt="QueueLess" class="hidden dark:block h-12 sm:h-14 w-auto mx-auto object-contain" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
            </a>
            <span class="inline-flex px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[10px] font-bold font-mono">STUDENT PORTAL</span>
            <p class="text-xs text-slate-500 dark:text-slate-400">Sign in to join queues, track live positions and manage tickets.</p>
        </div>

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="login_role" value="STUDENT">
            <div>
                <label for="student-email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Student Email</label>
                <input id="student-email" type="email" name="email" value="{{ old('email', 'student1@queueless.local') }}" required
                       class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389]">
                @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="student-password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Password</label>
                <input id="student-password" type="password" name="password" value="password" required
                       class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389]">
            </div>
            <label class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-[#00c389] focus:ring-[#00c389]"> Remember me
            </label>
            <button type="submit" class="w-full py-3.5 rounded-full bg-[#00c389] hover:bg-[#00ab78] text-white font-black text-sm shadow-md shadow-emerald-500/25 transition">Sign In as Student</button>
        </form>

        <p class="text-center text-xs text-slate-500 dark:text-slate-400 pt-2">
            <span>New student?</span>
            <a href="{{ route('register') }}" class="font-bold text-[#00c389] hover:underline">New student? Register</a>
        </p>
    </div>
</div>
@endsection
