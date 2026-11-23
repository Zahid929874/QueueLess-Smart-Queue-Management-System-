@extends('layouts.app', ['title' => 'Queue History'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="bg-white dark:bg-[#0f172a] rounded-3xl p-6 sm:p-7 border border-slate-100 dark:border-slate-800 shadow-card flex flex-col sm:flex-row sm:items-center justify-between gap-4 interactive-card">
        <div class="space-y-1">
            <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-[#00c389]/10 text-[#00c389] border border-[#00c389]/20 inline-flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="archive" class="w-3.5 h-3.5"></i> Activity History
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">My University Service Receipts</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Historical records of your served, completed, and processed university tickets</p>
        </div>
        <a href="{{ route('student.dashboard') }}" class="px-5 py-2.5 rounded-full bg-slate-50 hover:bg-slate-100 dark:bg-[#142034] dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 transition-all hover:scale-105 active:scale-95 flex items-center gap-2 shadow-xs self-start sm:self-auto">
            <i data-lucide="radio" class="w-4 h-4 text-[#00c389]"></i> Live Active Pass
        </a>
    </div>

    <!-- Receipts List -->
    <div class="bg-white dark:bg-[#0f172a] border border-slate-100 dark:border-slate-800 rounded-3xl overflow-hidden shadow-card interactive-card">
        @if($tickets->count() > 0)
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($tickets as $t)
                    <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/60 dark:hover:bg-[#142034]/60 transition group">
                        <div class="flex items-start gap-4">
                            <span class="w-16 h-16 rounded-2xl bg-[#00c389]/10 border border-[#00c389]/25 text-[#00c389] font-mono font-black text-base flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                                {{ $t->ticket_number }}
                            </span>
                            <div>
                                <div class="flex items-center gap-2.5 flex-wrap">
                                    <h3 class="font-bold text-base text-slate-900 dark:text-white">{{ $t->service->name }}</h3>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono border shadow-2xs {{ $t->status === 'COMPLETED' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : ($t->status === 'CANCELLED' ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700') }}">
                                        {{ $t->status }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    <strong class="font-semibold text-slate-700 dark:text-slate-200">{{ $t->department->name }}</strong> • {{ $t->counter ? $t->counter->name : 'No Desk Assigned' }}
                                </p>
                                <span class="text-[11px] text-slate-400 block mt-1.5 font-mono flex items-center gap-1.5">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                    Joined: {{ $t->joined_at->format('M d, Y • h:i A') }}
                                </span>
                            </div>
                        </div>

                        <div class="text-left sm:text-right text-xs self-start sm:self-center bg-slate-50 dark:bg-[#142034] sm:bg-transparent dark:sm:bg-transparent p-3 sm:p-0 rounded-xl w-full sm:w-auto">
                            @if($t->completed_at && $t->called_at)
                                <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Handling Time</span>
                                <span class="font-mono font-black text-[#00c389] text-sm">
                                    {{ round($t->completed_at->diffInSeconds($t->called_at) / 60, 1) }} min
                                </span>
                            @else
                                <span class="text-slate-500 dark:text-slate-400 text-xs font-mono font-medium">{{ $t->status }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/30 dark:bg-[#142034]/30">
                {{ $tickets->links() }}
            </div>
        @else
            <div class="py-20 text-center text-slate-500 dark:text-slate-400 text-xs space-y-3">
                <div class="w-16 h-16 rounded-2xl bg-[#00c389]/10 border border-[#00c389]/20 text-[#00c389] flex items-center justify-center mx-auto shadow-xs">
                    <i data-lucide="inbox" class="w-8 h-8"></i>
                </div>
                <p class="text-base font-bold text-slate-800 dark:text-white">No past queue records found for this account.</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">When you request service tokens and complete counter visits, your verifiable receipts will be logged here.</p>
                <div class="pt-2">
                    <a href="{{ route('student.join') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-[#00c389] hover:bg-[#00ab78] text-white text-xs font-bold transition hover:scale-105 active:scale-95 shadow-md shadow-emerald-500/20">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i> Take a New Token
                    </a>
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
