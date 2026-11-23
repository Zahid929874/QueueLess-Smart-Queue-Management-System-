@extends('layouts.app', ['title' => 'Operator Station Console'])

@section('content')
@php
    $departmentApplicationCounts = $allDepartments->map(fn ($office) => [
        'id' => $office->id,
        'count' => $office->waiting_applications_count,
    ])->values();
@endphp
<div x-data="operatorConsole()" x-init="init()" @keydown.window="handleHotkeys($event)" class="space-y-6">

    <!-- Operator & Counter Header -->
    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 sm:p-6 border border-slate-100 dark:border-slate-800 shadow-card interactive-card flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-mint-50 dark:bg-emerald-950/40 border border-mint-200 dark:border-emerald-800/60 text-[#00c389] flex items-center justify-center shadow-xs">
                <i data-lucide="headset" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-lg font-black text-slate-900 dark:text-white font-display tracking-tight"
                        x-text="counter ? (counter.name + ' — ' + department.name) : 'Service Counter'"></h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono border"
                          :class="counter && counter.status === 'BUSY' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800' : (counter && counter.status === 'BREAK' ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border-rose-200 dark:border-rose-800' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800')"
                          x-text="counter ? counter.status : 'OFFLINE'"></span>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">
                    Officer: <strong class="text-slate-700 dark:text-slate-200">{{ auth()->user()->name }}</strong>
                    ({{ auth()->user()->email }})
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button @click="toggleDeskStatus()"
                    class="px-3.5 py-2 rounded-full border text-xs font-bold transition flex items-center gap-1.5 shadow-2xs"
                    :class="counter && counter.status === 'BREAK' ? 'border-amber-300 bg-amber-50 text-amber-900 dark:bg-amber-950/40 dark:border-amber-700 dark:text-amber-300' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:bg-[#142034] dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800'">
                <i data-lucide="coffee" class="w-3.5 h-3.5" :class="counter && counter.status === 'BREAK' ? 'text-amber-600' : 'text-slate-400'"></i>
                <span x-text="counter && counter.status === 'BREAK' ? 'Resume Desk' : 'Take Break'"></span>
            </button>

            <button @click="openWalkInModal = true"
                    class="px-3.5 py-2 rounded-full border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 dark:bg-[#142034] dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800 text-xs font-bold transition flex items-center gap-1.5">
                <i data-lucide="ticket-plus" class="w-3.5 h-3.5 text-[#00c389]"></i> Issue Walk-In
            </button>

            <button @click="voiceEnabled = !voiceEnabled"
                    class="px-3.5 py-2 rounded-full border text-xs font-bold transition flex items-center gap-1.5"
                    :class="voiceEnabled ? 'border-emerald-300 bg-emerald-50 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-700 dark:text-emerald-300' : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50 dark:bg-[#142034] dark:border-slate-700 dark:text-slate-400'">
                <i :data-lucide="voiceEnabled ? 'volume-2' : 'volume-x'" class="w-3.5 h-3.5"></i>
                <span x-text="voiceEnabled ? 'Voice ON' : 'Muted'"></span>
            </button>

            <select @change="changeCounter($event.target.value)"
                    class="px-3.5 py-2 rounded-full border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-[#142034] focus:outline-none focus:border-[#00c389]">
                @foreach($allDepartments as $dept)
                    <optgroup label="{{ $dept->name }} ({{ $dept->code }})">
                        @foreach($dept->counters as $c)
                            <option value="{{ $c->id }}" {{ ($assignment && $assignment->counter_id == $c->id) ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $dept->code }})
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Campus Service Desks -->
    <section class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-[#0f172a] shadow-card overflow-hidden">
        <div class="px-5 sm:px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-black text-slate-900 dark:text-white">Campus Service Desks</h2>
                    <p class="mt-0.5 text-[10px] text-slate-400">Monitor every service office and its live student waiting line.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="deskView = 'grid'"
                            class="px-3 py-1.5 rounded-full border text-[10px] font-bold flex items-center gap-1.5"
                            :class="deskView === 'grid' ? 'border-[#00c389] bg-mint-50 text-[#00c389] dark:bg-emerald-950/40 dark:border-emerald-700' : 'border-slate-200 text-slate-500 dark:border-slate-700 dark:text-slate-400'">
                        <i data-lucide="layout-grid" class="w-3 h-3"></i> Grid View
                    </button>
                    <button type="button" @click="deskView = 'list'"
                            class="px-3 py-1.5 rounded-full border text-[10px] font-bold flex items-center gap-1.5"
                            :class="deskView === 'list' ? 'border-[#00c389] bg-mint-50 text-[#00c389] dark:bg-emerald-950/40 dark:border-emerald-700' : 'border-slate-200 text-slate-500 dark:border-slate-700 dark:text-slate-400'">
                        <i data-lucide="list" class="w-3 h-3"></i> Desk List
                    </button>
                </div>
            </div>
            <div class="mt-3 relative max-w-md">
                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" x-model="deskSearch" placeholder="Search office..."
                       class="w-full pl-9 pr-3 py-2 rounded-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-[#142034] text-xs text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:border-[#00c389]">
            </div>
        </div>

        <div class="p-4 sm:p-5"
             :class="deskView === 'grid' ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3' : 'space-y-2'">
            @foreach($allDepartments as $office)
                @php
                    $firstCounter = $office->counters->first();
                @endphp
                <article x-show="matchesDeskSearch('{{ $office->name }}', '{{ $office->code }}')"
                         class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-[#142034]/55 p-4 flex flex-col justify-between gap-4"
                         :class="deskView === 'list' ? 'sm:flex-row sm:items-center' : ''">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-900 text-white dark:bg-slate-700 text-[10px] font-mono font-bold">{{ $office->code }}</span>
                            <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800 text-[10px] font-bold font-mono">
                                <i data-lucide="users" class="w-3 h-3 inline mr-1"></i>
                                <span x-text="departmentApplicationCount({{ $office->id }}, {{ $office->waiting_applications_count }})"></span> / Line
                            </span>
                        </div>
                        <h3 class="mt-3 text-sm font-black text-slate-900 dark:text-white">{{ $office->name }}</h3>
                        <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                            <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                            {{ $office->location ?: 'Campus Service Building' }}
                        </p>
                        <div class="mt-2 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full {{ $office->waiting_applications_count > 0 ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                            <span class="text-[9px] font-bold uppercase tracking-wider {{ $office->waiting_applications_count > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">Available</span>
                        </div>
                    </div>
                    @if($firstCounter)
                        <button type="button" @click="changeCounter('{{ $firstCounter->id }}')"
                                class="px-4 py-2 rounded-full bg-[#00c389] hover:bg-emerald-600 text-white text-[10px] font-bold flex items-center justify-center gap-1.5 shrink-0">
                            <i data-lucide="headset" class="w-3.5 h-3.5"></i>
                            <span>{{ ($assignment && $assignment->counter_id == $firstCounter->id) ? 'At Desk' : 'Open Desk' }}</span>
                        </button>
                    @endif
                </article>
            @endforeach
        </div>
    </section>

    <!-- Active Counter and Waiting Queue -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <div class="lg:col-span-7">
            <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-6 sm:p-7 border border-slate-100 dark:border-slate-800 shadow-card relative">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800 gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#00c389] flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#00c389] animate-ping"></span>
                        Active Counter Workstation Terminal
                    </span>
                    <div class="flex flex-wrap items-center gap-1 text-[10px] font-mono">
                        <span class="text-slate-400">Shortcuts:</span>
                        <kbd class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold">Space (Next)</kbd>
                        <kbd class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold">Alt+S (Start)</kbd>
                        <kbd class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold">Alt+D (Done)</kbd>
                        <kbd class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold">Alt+R (Recall)</kbd>
                    </div>
                </div>

                <template x-if="currentTicket">
                    <div class="space-y-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block font-mono">Current Token In-Seat</span>
                                <h2 class="text-6xl sm:text-7xl font-black font-mono tracking-tight text-slate-900 dark:text-white mt-0.5"
                                    x-text="currentTicket.ticket_number"></h2>
                                <div class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-200"
                                     x-text="currentTicket.service ? currentTicket.service.name : ''"></div>
                                <div class="text-xs text-slate-400 mt-1 flex flex-wrap items-center gap-2">
                                    <span>Client:
                                        <strong class="text-slate-700 dark:text-slate-200"
                                                x-text="currentTicket.student_profile && currentTicket.student_profile.user ? currentTicket.student_profile.user.name : 'Walk-In Client'"></strong>
                                        (<span class="font-mono" x-text="currentTicket.student_profile ? currentTicket.student_profile.student_id_number : 'N/A'"></span>)
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-mono font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">ACADEMIC STANDING: ACTIVE</span>
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-mono font-bold bg-mint-50 dark:bg-emerald-950/40 text-[#00c389] border border-mint-200 dark:border-emerald-800">FINANCIAL HOLD: CLEARED</span>
                                </div>
                            </div>

                            <div class="text-right p-4 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-100 dark:border-slate-800 shrink-0 font-mono">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Service Timer</span>
                                <div class="text-3xl font-black text-slate-900 dark:text-white" x-text="formatTimer(timerSeconds)"></div>
                                <span class="text-[10px] font-semibold block mt-1"
                                      :class="timerSeconds > 480 ? 'text-rose-600 font-bold' : 'text-slate-400'">Target SLA: ~8 min</span>
                            </div>
                        </div>

                        <div class="p-3 rounded-xl text-xs font-semibold flex items-center justify-between border"
                             :class="currentTicket.status === 'CALLED' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'">
                            <span class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full animate-ping"
                                      :class="currentTicket.status === 'CALLED' ? 'bg-amber-500' : 'bg-emerald-500'"></span>
                                Current Status: <strong x-text="currentTicket.status"></strong>
                            </span>
                            <span class="text-[11px] font-mono text-slate-500 dark:text-slate-400"
                                  x-text="'Called At: ' + (currentTicket.called_at ? new Date(currentTicket.called_at).toLocaleTimeString() : 'Now')"></span>
                        </div>

                        <div x-show="currentTicket.status === 'SERVING'">
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1">Resolution Remarks</label>
                            <input type="text" x-model="resolutionNotes"
                                   placeholder="e.g. Fees cleared, slip verified, documents attested"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#142034] text-xs text-slate-800 dark:text-white focus:outline-none focus:border-[#00c389]">
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                <span class="text-[10px] font-mono text-slate-400 self-center">Quick tags:</span>
                                <button type="button" @click="resolutionNotes = 'Fee slip verified & stamped'" class="px-2.5 py-0.5 rounded-md text-[10px] font-mono bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">+ Fee Stamped</button>
                                <button type="button" @click="resolutionNotes = 'Transcript documents issued & signed'" class="px-2.5 py-0.5 rounded-md text-[10px] font-mono bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">+ Transcript Handed</button>
                                <button type="button" @click="resolutionNotes = 'Student identity attested'" class="px-2.5 py-0.5 rounded-md text-[10px] font-mono bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">+ Attested</button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 pt-2">
                            <button @click="recall()" class="py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs flex items-center justify-center gap-1.5">
                                <i data-lucide="volume-2" class="w-3.5 h-3.5 text-[#00c389]"></i> Recall
                            </button>
                            <button x-show="currentTicket.status === 'CALLED'" @click="startService()" class="py-2.5 px-3 rounded-xl bg-[#00c389] hover:bg-emerald-600 text-white font-bold text-xs flex items-center justify-center gap-1.5">
                                <i data-lucide="play" class="w-3.5 h-3.5"></i> Start
                            </button>
                            <button @click="completeService()" class="py-2.5 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center justify-center gap-1.5">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-[#00c389]"></i> Done
                            </button>
                            <button @click="markNoShow()" class="py-2.5 px-3 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 font-bold text-xs border border-rose-200 dark:border-rose-800 flex items-center justify-center gap-1.5">
                                <i data-lucide="user-x" class="w-3.5 h-3.5"></i> No-Show
                            </button>
                            <button @click="openTransferModal = true" class="py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs flex items-center justify-center gap-1.5">
                                <i data-lucide="arrow-right-left" class="w-3.5 h-3.5 text-slate-500"></i> Transfer
                            </button>
                        </div>
                    </div>
                </template>

                <template x-if="!currentTicket">
                    <div class="py-12 text-center space-y-4">
                        <div class="w-16 h-16 rounded-2xl bg-mint-50 dark:bg-emerald-950/40 border border-mint-100 dark:border-emerald-800/60 text-[#00c389] flex items-center justify-center mx-auto">
                            <i data-lucide="user-check" class="w-8 h-8"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Counter Ready for Next Student</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto mt-1">
                                Press <kbd class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-mono">Space</kbd> to call the next waiting token.
                            </p>
                        </div>
                        <button @click="callNext()" :disabled="calling"
                                class="px-8 py-3.5 rounded-full bg-[#00c389] hover:bg-emerald-600 active:scale-95 disabled:opacity-50 text-white font-bold text-sm shadow-md shadow-mint inline-flex items-center gap-2.5">
                            <span x-show="calling" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <i x-show="!calling" data-lucide="megaphone" class="w-4 h-4"></i>
                            <span x-text="calling ? 'Calling Token...' : 'CALL NEXT STUDENT (Space)'"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Waiting Queue -->
        <div class="lg:col-span-5">
            <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-card space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Department Line</h2>
                        <p class="text-xs text-slate-400" x-text="department ? department.name : ''"></p>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-mint-50 dark:bg-emerald-950/40 text-[#00c389] border border-mint-200 dark:border-emerald-800 text-xs font-bold font-mono"
                          x-text="waitingQueue.length + ' Waiting'"></span>
                </div>

                <div class="relative">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" x-model="queueSearch" placeholder="Filter token or service..."
                           class="w-full pl-8 pr-3 py-1.5 rounded-full border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-[#142034] focus:outline-none focus:border-[#00c389]">
                </div>

                <template x-if="nextTicket">
                    <div class="p-3.5 rounded-xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="px-2.5 py-1 rounded-lg bg-amber-100 dark:bg-amber-900/60 text-amber-900 dark:text-amber-200 font-mono font-bold text-xs"
                                  x-text="nextTicket.ticket_number"></span>
                            <div>
                                <span class="font-bold text-xs text-slate-900 dark:text-white block" x-text="nextTicket.service.name"></span>
                                <span class="text-[10px] text-amber-700 dark:text-amber-400">Next in line</span>
                            </div>
                        </div>
                        <button @click="callNext()" :disabled="calling || currentTicket"
                                class="px-3.5 py-1.5 rounded-full bg-[#00c389] disabled:opacity-40 text-white font-bold text-xs">Call</button>
                    </div>
                </template>

                <div class="space-y-2 max-h-[460px] overflow-y-auto pr-1">
                    <template x-for="(item, idx) in filteredWaitingQueue()" :key="item.id">
                        <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-800/80 bg-slate-50/60 dark:bg-[#142034]/60 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="font-bold text-slate-400 font-mono text-[11px]" x-text="'#' + (idx + 1)"></span>
                                <span class="font-bold text-slate-800 dark:text-slate-200 font-mono" x-text="item.ticket_number"></span>
                                <span class="text-slate-500 dark:text-slate-400 text-[11px]" x-text="item.service.name"></span>
                            </div>
                            <span class="text-[10px] text-slate-400 font-mono"
                                  x-text="new Date(item.created_at).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})"></span>
                        </div>
                    </template>
                    <template x-if="waitingQueue.length === 0">
                        <div class="py-12 text-center text-slate-400 text-xs">
                            <i data-lucide="check-circle-2" class="w-8 h-8 mx-auto text-[#00c389] mb-2"></i>
                            Queue is currently clear! No students waiting.
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div x-show="openTransferModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
        <div @click.away="openTransferModal = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Transfer Ticket to Another Department</h3>
            <select x-model="transferDeptId" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-xs">
                @foreach($allDepartments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                @endforeach
            </select>
            <div class="flex justify-end gap-2">
                <button @click="openTransferModal = false" class="px-4 py-2 rounded-full border border-slate-200 dark:border-slate-700 text-xs">Cancel</button>
                <button @click="submitTransfer()" class="px-5 py-2 rounded-full bg-[#00c389] text-white text-xs font-bold">Forward Ticket</button>
            </div>
        </div>
    </div>

    <div x-show="openWalkInModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
        <div @click.away="openWalkInModal = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Issue Walk-In Queue Slip</h3>
            <select x-model="walkInServiceId" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-xs">
                <template x-for="svc in (department && department.services ? department.services : [])" :key="svc.id">
                    <option :value="svc.id" x-text="svc.name"></option>
                </template>
            </select>
            <input type="text" x-model="walkInName" placeholder="Visitor Name (Optional)"
                   class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-xs">
            <div class="flex justify-end gap-2">
                <button @click="openWalkInModal = false" class="px-4 py-2 rounded-full border border-slate-200 dark:border-slate-700 text-xs">Cancel</button>
                <button @click="submitWalkIn()" class="px-5 py-2 rounded-full bg-[#00c389] text-white text-xs font-bold">Generate Slip</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function operatorConsole() {
    return {
        counter: @json($assignment?->counter),
        department: @json($assignment?->counter?->department),
        currentTicket: @json($currentTicket),
        waitingQueue: @json($waitingQueue),
        nextTicket: @json($waitingQueue->first()),
        calling: false,
        timerSeconds: 0,
        timerInterval: null,
        openTransferModal: false,
        openWalkInModal: false,
        walkInServiceId: null,
        walkInName: '',
        resolutionNotes: '',
        transferDeptId: '{{ $allDepartments->first()->id ?? 1 }}',
        voiceEnabled: true,
        queueSearch: '',
        pollTimer: null,
        deskSearch: '',
        deskView: 'grid',
        departmentApplicationCounts: @json($departmentApplicationCounts),

        departmentApplicationCount(officeId, fallback) {
            const office = this.departmentApplicationCounts.find(item => item.id === officeId);
            return office ? office.count : fallback;
        },

        matchesDeskSearch(name, code) {
            if (!this.deskSearch) return true;
            const query = this.deskSearch.toLowerCase();
            return name.toLowerCase().includes(query) || code.toLowerCase().includes(query);
        },

        filteredWaitingQueue() {
            if (!this.queueSearch) return this.waitingQueue;
            const q = this.queueSearch.toLowerCase();
            return this.waitingQueue.filter(item =>
                (item.ticket_number || '').toLowerCase().includes(q) ||
                (item.service?.name || '').toLowerCase().includes(q)
            );
        },

        init() {
            lucide.createIcons();
            this.initTimer();
            if (this.department?.services?.length) this.walkInServiceId = this.department.services[0].id;
            this.pollTimer = setInterval(() => this.syncQueue(), 2500);
        },

        handleHotkeys(e) {
            if (e.code === 'Space' && !this.currentTicket && !['INPUT','TEXTAREA','SELECT'].includes(e.target.tagName)) {
                e.preventDefault(); this.callNext();
            }
            if (e.altKey && e.code === 'KeyS' && this.currentTicket?.status === 'CALLED') {
                e.preventDefault(); this.startService();
            }
            if (e.altKey && e.code === 'KeyD' && this.currentTicket) {
                e.preventDefault(); this.completeService();
            }
            if (e.altKey && e.code === 'KeyR' && this.currentTicket) {
                e.preventDefault(); this.recall();
            }
        },

        initTimer() {
            if (this.currentTicket) {
                const startTime = this.currentTicket.served_at || this.currentTicket.called_at;
                this.timerSeconds = startTime ? Math.max(0, Math.floor((Date.now() - new Date(startTime).getTime()) / 1000)) : 0;
            }
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timerInterval = setInterval(() => {
                if (this.currentTicket) this.timerSeconds++;
            }, 1000);
        },

        formatTimer(sec) {
            return String(Math.floor(sec / 60)).padStart(2, '0') + ':' + String(sec % 60).padStart(2, '0');
        },

        headers(extra = {}) {
            return {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                ...extra
            };
        },

        async toggleDeskStatus() {
            const res = await fetch('/api/operator/toggle-status', {method:'POST', headers:this.headers()});
            const json = await res.json();
            if (json.success) {
                this.counter = json.data.counter;
                window.showToast?.(json.message, 'info');
            }
        },

        async syncQueue() {
            const res = await fetch('/api/operator/queue', {headers:{'Accept':'application/json'}});
            if (!res.ok) return;
            const json = await res.json();
            if (json.success) {
                const oldId = this.currentTicket?.id;
                this.counter = json.data.counter;
                this.department = json.data.department;
                this.currentTicket = json.data.current_ticket;
                this.waitingQueue = json.data.waiting_queue;
                this.nextTicket = json.data.next_ticket;
                if (json.data.department_application_counts) {
                    this.departmentApplicationCounts = json.data.department_application_counts.map(item => ({
                        id: item.id,
                        count: item.count,
                    }));
                }
                if (oldId !== this.currentTicket?.id) this.initTimer();
                this.$nextTick(() => lucide.createIcons());
            }
        },

        async changeCounter(counterId) {
            const res = await fetch('/api/operator/assign-counter', {
                method:'POST', headers:this.headers(), body:JSON.stringify({counter_id: counterId})
            });
            const json = await res.json();
            if (json.success) window.location.reload();
        },

        async callNext() {
            this.calling = true;
            try {
                const res = await fetch('/api/operator/call-next', {method:'POST', headers:this.headers()});
                const json = await res.json();
                if (json.success) {
                    this.currentTicket = json.data.ticket;
                    this.timerSeconds = 0;
                    if (this.currentTicket && this.voiceEnabled && window.speechSynthesis) {
                        window.speechSynthesis.cancel();
                        window.speechSynthesis.speak(new SpeechSynthesisUtterance(
                            `Token ${this.currentTicket.ticket_number}, please proceed to ${this.counter.name}.`
                        ));
                    }
                    await this.syncQueue();
                } else window.showToast?.(json.message, 'info');
            } finally {
                this.calling = false;
            }
        },

        async recall() {
            const res = await fetch('/api/operator/recall', {method:'POST', headers:this.headers()});
            const json = await res.json();
            if (json.success) window.showToast?.('Ticket recalled and re-announced', 'success');
        },

        async startService() {
            const res = await fetch('/api/operator/start', {method:'POST', headers:this.headers()});
            const json = await res.json();
            if (json.success) {
                this.currentTicket.status = 'SERVING';
                this.initTimer();
                window.showToast?.('Service started', 'info');
            }
        },

        async completeService() {
            const res = await fetch('/api/operator/complete', {
                method:'POST', headers:this.headers(), body:JSON.stringify({notes:this.resolutionNotes})
            });
            const json = await res.json();
            if (json.success) {
                const ticket = this.currentTicket?.ticket_number;
                this.currentTicket = null;
                this.timerSeconds = 0;
                this.resolutionNotes = '';
                await this.syncQueue();
                window.showToast?.(`Ticket ${ticket} completed`, 'success');
            }
        },

        async markNoShow() {
            if (!confirm('Confirm marking client as No-Show?')) return;
            const res = await fetch('/api/operator/no-show', {method:'POST', headers:this.headers()});
            const json = await res.json();
            if (json.success) {
                this.currentTicket = null;
                await this.syncQueue();
                window.showToast?.('Ticket marked as No-Show', 'info');
            }
        },

        async submitTransfer() {
            const res = await fetch('/api/operator/transfer', {
                method:'POST', headers:this.headers(), body:JSON.stringify({department_id:this.transferDeptId})
            });
            const json = await res.json();
            if (json.success) {
                this.openTransferModal = false;
                this.currentTicket = null;
                await this.syncQueue();
                window.showToast?.(json.message, 'success');
            }
        },

        async submitWalkIn() {
            const res = await fetch('/api/operator/walk-in', {
                method:'POST', headers:this.headers(),
                body:JSON.stringify({service_id:this.walkInServiceId, guest_name:this.walkInName})
            });
            const json = await res.json();
            if (json.success) {
                this.openWalkInModal = false;
                this.walkInName = '';
                await this.syncQueue();
                window.showToast?.(`Walk-in ticket ${json.data.ticket.ticket_number} created`, 'success');
            }
        }
    }
}
</script>
@endpush
@endsection
