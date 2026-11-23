@extends('layouts.app', ['title' => 'Admin Invite Verification'])

@section('content')
<div class="max-w-md mx-auto py-10 sm:py-16">
    <div class="bg-white/90 dark:bg-[#0f172a]/95 backdrop-blur-xl rounded-3xl p-8 sm:p-9 border border-rose-100 dark:border-rose-900/50 shadow-card space-y-6">
        <div class="text-center space-y-3">
            <a href="{{ route('home') }}" class="inline-block" title="QueueLess">
                <img src="{{ asset('images/logo-light.png') }}" alt="QueueLess" class="dark:hidden h-12 w-auto mx-auto" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
                <img src="{{ asset('images/logo-transparent.png') }}" alt="QueueLess" class="hidden dark:block h-12 w-auto mx-auto" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
            </a>
            <span class="inline-flex px-3 py-1 rounded-full bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-[10px] font-bold font-mono">ADMIN INVITE GATE</span>
        </div>

        @if($registrationAvailable)
            <form method="POST" action="{{ route('admin.register.verify') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="invite_token" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Invite Token</label>
                    <input id="invite_token" name="invite_token" type="text" value="{{ old('invite_token') }}" placeholder="Enter your admin invite" required
                           class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm font-mono focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500">
                    @error('invite_token')
                        <p class="text-xs text-rose-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full py-3.5 rounded-full bg-slate-900 hover:bg-slate-800 text-white font-black text-sm shadow-md transition">
                    Verify Invite
                </button>
            </form>
        @else
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-center">
                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">Administrator account already exists</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Only one administrator can be registered.</p>
            </div>
        @endif

        <p class="text-center text-xs text-slate-500 dark:text-slate-400 pt-2">
            <a href="{{ route('admin.login') }}" class="font-bold text-rose-600 hover:underline">Back to Admin Sign In</a>
        </p>
    </div>
</div>
@endsection
