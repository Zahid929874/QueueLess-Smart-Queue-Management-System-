@extends('layouts.app', ['title' => 'Operator Overview'])

@section('content')
@php
    $queueStatusTotal = $waitingTickets + $inServiceTickets + $servedToday;
    $waitingShare = $queueStatusTotal > 0 ? round(($waitingTickets / $queueStatusTotal) * 100) : 0;
    $serviceShare = $queueStatusTotal > 0 ? round(($inServiceTickets / $queueStatusTotal) * 100) : 0;
    $maxWaiting = max(1, (int) $departments->max('waiting_count'));
@endphp

<div class="space-y-6">
    <section class="rounded-3xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-[#0f172a] p-5 sm:p-7 shadow-card">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Live operations
                    </span>
                    <span class="text-xs text-slate-400">{{ now()->format('D, M j, Y') }}</span>
                </div>
                <h1 class="mt-3 text-2xl font-black tracking-tight text-slate-900 dark:text-white sm:text-3xl">
                    Operator Overview
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Welcome, {{ $user->name }}. Monitor live campus queues and service desk activity.
                </p>
            </div>
            <a href="{{ route('operator.console') }}"
               class="inline-flex items-center justify-center gap-2 rounded-full bg-[#00c389] px-5 py-3 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-600">
                <i data-lucide="headset" class="h-4 w-4"></i>
                Open Counter Console
            </a>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Queue summary">
        <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-[#0f172a]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Waiting in queue</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-300"><i data-lucide="users" class="h-4 w-4"></i></span>
            </div>
            <p class="mt-4 text-3xl font-black text-slate-900 dark:text-white">{{ number_format($waitingTickets) }}</p>
            <p class="mt-1 text-xs text-slate-400">Across active campus service units</p>
        </article>

        <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-[#0f172a]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Active service desks</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300"><i data-lucide="monitor-check" class="h-4 w-4"></i></span>
            </div>
            <p class="mt-4 text-3xl font-black text-slate-900 dark:text-white">
                {{ number_format($departments->sum('active_counters_count')) }}
                <span class="text-base font-semibold text-slate-400">/ {{ number_format($departments->sum('total_counters_count')) }}</span>
            </p>
            <p class="mt-1 text-xs text-slate-400">Available or currently serving</p>
        </article>

        <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-[#0f172a]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Served today</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-950/40 dark:text-sky-300"><i data-lucide="badge-check" class="h-4 w-4"></i></span>
            </div>
            <p class="mt-4 text-3xl font-black text-slate-900 dark:text-white">{{ number_format($servedToday) }}</p>
            <p class="mt-1 text-xs text-slate-400">Completed queue tickets today</p>
        </article>

        <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-[#0f172a]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estimated wait</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-300"><i data-lucide="clock-3" class="h-4 w-4"></i></span>
            </div>
            <p class="mt-4 text-3xl font-black text-slate-900 dark:text-white">
                {{ $averageWaitMinutes === null ? '—' : number_format((float) $averageWaitMinutes, 1) . 'm' }}
            </p>
            <p class="mt-1 text-xs text-slate-400">Average estimate for waiting tickets</p>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-[#0f172a] xl:col-span-2">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-sm font-black text-slate-900 dark:text-white">Queue flow by service unit</h2>
                    <p class="mt-1 text-xs text-slate-400">Live waiting volume and desk availability</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-300">Live</span>
            </div>

            @if($departments->isEmpty())
                <div class="mt-5 rounded-xl border border-dashed border-slate-200 p-6 text-center dark:border-slate-700">
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200">No service unit is assigned yet</p>
                    <p class="mt-1 text-xs text-slate-400">Your dashboard will show queue activity after an administrator assigns your desk.</p>
                </div>
            @else
                <div class="mt-5 space-y-4">
                    @foreach($departments as $department)
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <span class="font-mono text-[10px] font-bold text-[#00a875]">{{ $department->code }}</span>
                                    <span class="ml-2 truncate text-xs font-bold text-slate-700 dark:text-slate-200">{{ $department->name }}</span>
                                </div>
                                <span class="shrink-0 text-[10px] font-semibold text-slate-500 dark:text-slate-400">
                                    {{ $department->active_counters_count }}/{{ $department->total_counters_count }} desks
                                    <span class="mx-1 text-slate-300">·</span>
                                    {{ $department->waiting_count }} waiting
                                </span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div class="h-full rounded-full bg-gradient-to-r from-[#00c389] to-teal-400 transition-all"
                                     style="width: {{ min(100, ($department->waiting_count / $maxWaiting) * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </article>

        <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-[#0f172a]">
            <h2 class="text-sm font-black text-slate-900 dark:text-white">Queue status</h2>
            <p class="mt-1 text-xs text-slate-400">Current service snapshot</p>
            <div class="mt-5 flex items-center justify-center">
                <div class="flex h-36 w-36 items-center justify-center rounded-full"
                     role="img"
                     aria-label="Queue status: {{ $waitingTickets }} waiting, {{ $inServiceTickets }} in service, {{ $servedToday }} served today"
                     style="background: conic-gradient(#f59e0b 0 {{ $waitingShare }}%, #00c389 {{ $waitingShare }}% {{ $waitingShare + $serviceShare }}%, #6366f1 {{ $waitingShare + $serviceShare }}% 100%)">
                    <div class="flex h-24 w-24 flex-col items-center justify-center rounded-full bg-white dark:bg-[#0f172a]">
                        <span class="text-2xl font-black text-slate-900 dark:text-white">{{ number_format($queueStatusTotal) }}</span>
                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Tickets</span>
                    </div>
                </div>
            </div>
            <div class="mt-5 space-y-3">
                @foreach([
                    ['label' => 'Waiting', 'count' => $waitingTickets, 'color' => 'bg-amber-500'],
                    ['label' => 'In service', 'count' => $inServiceTickets, 'color' => 'bg-[#00c389]'],
                    ['label' => 'Served today', 'count' => $servedToday, 'color' => 'bg-indigo-500'],
                ] as $status)
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-2 text-slate-500 dark:text-slate-400"><span class="h-2 w-2 rounded-full {{ $status['color'] }}"></span>{{ $status['label'] }}</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ number_format($status['count']) }}</span>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-[#0f172a] xl:col-span-2">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-black text-slate-900 dark:text-white">Service desks</h2>
                    <p class="mt-1 text-xs text-slate-400">Desk status and current operator assignment</p>
                </div>
                <a href="{{ route('operator.console') }}" class="text-xs font-bold text-[#00a875] hover:text-emerald-700">Manage desk <span aria-hidden="true">→</span></a>
            </div>
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                @forelse($departments as $department)
                    @foreach($department->counters as $counter)
                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-[#142034]/60">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-bold text-slate-800 dark:text-slate-100">{{ $counter->name }}</p>
                                    <p class="mt-1 truncate text-[10px] text-slate-400">{{ $department->name }}</p>
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-1 text-[9px] font-bold {{ in_array($counter->status, ['AVAILABLE', 'BUSY'], true) ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                                    {{ str_replace('_', ' ', $counter->status) }}
                                </span>
                            </div>
                            <p class="mt-3 text-[10px] text-slate-500 dark:text-slate-400">
                                {{ $counter->currentAssignment?->user?->name ?? 'No operator assigned' }}
                            </p>
                        </div>
                    @endforeach
                @empty
                    <p class="col-span-full rounded-xl border border-dashed border-slate-200 p-5 text-center text-xs text-slate-400 dark:border-slate-700">No active service desks to display.</p>
                @endforelse
            </div>
        </article>

        <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-[#0f172a]">
            <div>
                <h2 class="text-sm font-black text-slate-900 dark:text-white">Live desk activity</h2>
                <p class="mt-1 text-xs text-slate-400">Recent queue events</p>
            </div>
            <div class="mt-4 space-y-4">
                @forelse($recentActivity as $event)
                    <div class="flex gap-3">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-[#00c389]"></span>
                        <div class="min-w-0 flex-1 border-b border-slate-100 pb-3 last:border-0 dark:border-slate-800">
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ ucwords(strtolower(str_replace('_', ' ', $event->event_type))) }}</p>
                            <p class="mt-1 truncate text-[10px] text-slate-500 dark:text-slate-400">
                                {{ $event->ticket?->ticket_number ?? 'Queue ticket' }}
                                @if($event->ticket?->department)
                                    · {{ $event->ticket->department->code }}
                                @endif
                                @if($event->ticket?->studentProfile?->user)
                                    · {{ $event->ticket->studentProfile->user->name }}
                                @endif
                            </p>
                            <p class="mt-1 text-[9px] text-slate-400">{{ $event->created_at?->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="rounded-xl border border-dashed border-slate-200 p-5 text-center text-xs text-slate-400 dark:border-slate-700">No recent queue activity.</p>
                @endforelse
            </div>
        </article>
    </section>
</div>
@endsection
