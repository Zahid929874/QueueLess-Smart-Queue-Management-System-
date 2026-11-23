@extends('layouts.app', ['title' => 'Clearance Pending'])

@section('content')
<div class="max-w-lg mx-auto py-12">
    <div class="bg-white dark:bg-[#0f172a] rounded-3xl p-8 sm:p-10 border border-slate-100 dark:border-slate-800 shadow-card text-center">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 border border-amber-200 dark:border-amber-800 flex items-center justify-center">
            <i data-lucide="clock-3" class="w-8 h-8"></i>
        </div>
        <h1 class="mt-5 text-xl font-black text-slate-900 dark:text-white">Clearance Pending</h1>
        <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">
            Your request has been sent to the admin desk. You will be notified once approved.
        </p>
        <a href="{{ route('operator.login') }}" class="inline-flex mt-6 px-6 py-2.5 rounded-full bg-[#00c389] text-white text-xs font-bold">
            Return to Officer Sign In
        </a>
    </div>
</div>
@endsection
