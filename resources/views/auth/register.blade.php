@extends('layouts.app', ['title' => 'Register Account'])

@section('content')
<div class="max-w-lg mx-auto py-6 sm:py-8">

    <div class="bg-white dark:bg-[#0f172a] rounded-3xl p-8 sm:p-9 border border-slate-100 dark:border-slate-800 shadow-card space-y-6 interactive-card">

        <div class="text-center space-y-3">
            <a href="{{ route('home') }}" class="inline-block transition-transform hover:scale-105 active:scale-95" title="QueueLess">
                <img src="{{ asset('images/logo-light.png') }}" alt="QueueLess" class="dark:hidden h-12 sm:h-14 w-auto mx-auto object-contain drop-shadow-xs" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
                <img src="{{ asset('images/logo-transparent.png') }}" alt="QueueLess" class="hidden dark:block h-12 sm:h-14 w-auto mx-auto object-contain drop-shadow-xs" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
            </a>
            <p class="text-xs text-slate-500 dark:text-slate-400">Join digital queues, track live positions, and receive instant dispatch alerts</p>
        </div>

        <form method="POST" action="{{ route('register.post') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Full Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Tanvir Hasan" required
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] placeholder-slate-400 transition">
                    @error('name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="student_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Member / Student ID</label>
                    <input id="student_id" type="text" name="student_id" value="{{ old('student_id') }}" placeholder="202601004" required
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm font-mono focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] placeholder-slate-400 transition">
                    @error('student_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Email Address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="student@university.edu" required
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] placeholder-slate-400 transition">
                @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="department" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Department / Faculty</label>
                    <input id="department" type="text" name="department" value="{{ old('department', 'Computer Science') }}" required
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] transition">
                </div>
                <div>
                    <label for="batch" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Batch / Group</label>
                    <input id="batch" type="text" name="batch" value="{{ old('batch', 'Batch 2026') }}"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] transition">
                </div>
            </div>

            <div>
                <label for="phone" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Phone Number (SMS & WhatsApp Alerts)</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', '01700000000') }}"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Password</label>
                    <input id="password" type="password" name="password" required
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] transition">
                    @error('password') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] transition">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-full bg-[#00c389] hover:bg-[#00ab78] text-white font-black text-sm tracking-wide shadow-md shadow-emerald-500/25 hover:shadow-emerald-500/40 transition hover:scale-[1.02] active:scale-95 mt-2">
                Register & Enter Portal
            </button>
        </form>

        <p class="text-center text-xs text-slate-500 dark:text-slate-400 pt-2">
            Already have an account?
            <a href="{{ route('login') }}" class="font-bold text-[#00c389] hover:underline">Sign In</a>
        </p>
    </div>

</div>
@endsection
