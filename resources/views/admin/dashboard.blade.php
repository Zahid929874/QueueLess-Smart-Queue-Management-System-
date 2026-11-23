@extends('layouts.app', ['title' => 'Admin Operations & Analytics'])

@section('content')
<div class="space-y-6" x-data="adminDashboard()" x-init="init()">

    <!-- Header & Quick Operational Actions -->
    <div class="bg-white dark:bg-[#0f172a] rounded-3xl p-6 sm:p-7 border border-slate-100 dark:border-slate-800 shadow-card flex flex-col sm:flex-row sm:items-center justify-between gap-5 interactive-card">
        <div class="space-y-1">
            <div class="flex items-center gap-2.5">
                <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/80 flex items-center gap-1.5 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    Administrator
                </span>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Real-Time Capacity & SLA Intelligence</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Campus Capacity & Operations Console</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Continuous M/M/c Erlang-C queue modeling, counter staffing, and automated SLA compliance tracking</p>
        </div>

        <!-- Management Tools: Export, New Dept, Reset, Refresh -->
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.export-csv') }}" class="px-4 py-2.5 rounded-full bg-slate-50 hover:bg-slate-100 dark:bg-[#142034] dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all hover:scale-105 active:scale-95 flex items-center gap-2 shadow-xs">
                <i data-lucide="download" class="w-4 h-4 text-[#00c389]"></i> Export CSV
            </a>

            <button @click="openNewDeptModal = true" class="px-5 py-2.5 rounded-full bg-[#00c389] hover:bg-[#00ab78] text-white text-xs font-bold transition-all hover:scale-105 active:scale-95 flex items-center gap-2 shadow-md shadow-emerald-500/20">
                <i data-lucide="plus" class="w-4 h-4"></i> Add Department
            </button>

            <button @click="resetQueues()" class="px-4 py-2.5 rounded-full bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800 text-xs font-bold transition-all hover:scale-105 active:scale-95 flex items-center gap-2 shadow-xs">
                <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset Queues
            </button>

            <button @click="refreshData()" class="p-2.5 rounded-full bg-slate-50 dark:bg-[#142034] border border-slate-200/80 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition-all hover:scale-105 active:scale-95 shadow-xs" title="Refresh Live Data">
                <i data-lucide="refresh-cw" class="w-4 h-4 text-slate-600 dark:text-slate-400 transition-transform duration-500" :class="refreshing ? 'animate-spin text-[#00c389]' : ''"></i>
            </button>
        </div>
    </div>

    <!-- 1. KPI Cards Row (7 Metrics - Slider Track Aesthetic matching HOMEAGE) -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3.5">
        <!-- Metric 1: Waiting -->
        <div class="bg-white dark:bg-[#0f172a] p-4 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-card hover:shadow-card-hover transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Waiting</span>
                <span class="w-7 h-7 rounded-xl bg-[#00c389]/10 text-[#00c389] flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </span>
            </div>
            <!-- Slider Track -->
            <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full mt-3 relative">
                <div class="h-full bg-[#00c389] rounded-full" :style="'width: ' + Math.min(100, ((metrics.waiting_students || 0) / 25) * 100) + '%'"></div>
                <div class="w-3 h-3 rounded-full bg-[#00c389] border-2 border-white dark:border-[#0f172a] shadow-xs absolute -top-0.5" :style="'left: calc(' + Math.min(95, ((metrics.waiting_students || 0) / 25) * 100) + '% - 6px)'"></div>
            </div>
            <span class="text-2xl font-black text-slate-900 dark:text-white font-mono mt-3 block" x-text="metrics.waiting_students ?? 0"></span>
            <div class="flex items-center gap-1.5 mt-1">
                <span class="text-[10px] font-bold text-slate-400">Across all offices</span>
            </div>
        </div>

        <!-- Metric 2: Active Desks -->
        <div class="bg-white dark:bg-[#0f172a] p-4 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-card hover:shadow-card-hover transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Active Desks</span>
                <span class="w-7 h-7 rounded-xl bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                    <i data-lucide="monitor" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full mt-3 relative">
                <div class="h-full bg-teal-500 rounded-full" :style="'width: ' + (((metrics.active_counters || 1) / (metrics.total_counters || 20)) * 100) + '%'"></div>
                <div class="w-3 h-3 rounded-full bg-teal-500 border-2 border-white dark:border-[#0f172a] shadow-xs absolute -top-0.5" :style="'left: calc(' + (((metrics.active_counters || 1) / (metrics.total_counters || 20)) * 100) + '% - 6px)'"></div>
            </div>
            <span class="text-2xl font-black text-teal-600 dark:text-teal-400 font-mono mt-3 block" x-text="(metrics.active_counters ?? 0) + '/' + (metrics.total_counters ?? 20)"></span>
            <div class="flex items-center gap-1.5 mt-1">
                <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.5 rounded">Staffed</span>
                <span class="text-[10px] text-slate-400">serving</span>
            </div>
        </div>

        <!-- Metric 3: Avg Wait Time -->
        <div class="bg-white dark:bg-[#0f172a] p-4 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-card hover:shadow-card-hover transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Avg Wait</span>
                <span class="w-7 h-7 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full mt-3 relative">
                <div class="h-full bg-emerald-500 rounded-full" :style="'width: ' + Math.min(100, ((metrics.avg_wait_time_min || 5.2) / 15) * 100) + '%'"></div>
                <div class="w-3 h-3 rounded-full bg-emerald-500 border-2 border-white dark:border-[#0f172a] shadow-xs absolute -top-0.5" :style="'left: calc(' + Math.min(95, ((metrics.avg_wait_time_min || 5.2) / 15) * 100) + '% - 6px)'"></div>
            </div>
            <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono mt-3 block" x-text="(metrics.avg_wait_time_min ?? 5.2) + 'm'"></span>
            <div class="flex items-center gap-1.5 mt-1">
                <span class="text-[10px] font-bold text-slate-400">M/M/c Erlang-C</span>
            </div>
        </div>

        <!-- Metric 4: Avg Service -->
        <div class="bg-white dark:bg-[#0f172a] p-4 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-card hover:shadow-card-hover transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Avg Service</span>
                <span class="w-7 h-7 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <i data-lucide="timer" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full mt-3 relative">
                <div class="h-full bg-indigo-500 rounded-full" style="width: 45%;"></div>
                <div class="w-3 h-3 rounded-full bg-indigo-500 border-2 border-white dark:border-[#0f172a] shadow-xs absolute -top-0.5 left-[42%]"></div>
            </div>
            <span class="text-2xl font-black text-slate-900 dark:text-white font-mono mt-3 block" x-text="(metrics.avg_service_time_min ?? 6.0) + 'm'"></span>
            <div class="flex items-center gap-1.5 mt-1">
                <span class="text-[10px] font-bold text-slate-400">Rolling mean</span>
            </div>
        </div>

        <!-- Metric 5: Served Today -->
        <div class="bg-white dark:bg-[#0f172a] p-4 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-card hover:shadow-card-hover transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Served Today</span>
                <span class="w-7 h-7 rounded-xl bg-[#00c389]/10 text-[#00c389] flex items-center justify-center">
                    <i data-lucide="check-check" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full mt-3 relative">
                <div class="h-full bg-[#00c389] rounded-full" :style="'width: ' + Math.min(100, ((metrics.completed_today || 0) / 100) * 100) + '%'"></div>
                <div class="w-3 h-3 rounded-full bg-[#00c389] border-2 border-white dark:border-[#0f172a] shadow-xs absolute -top-0.5" :style="'left: calc(' + Math.min(95, ((metrics.completed_today || 0) / 100) * 100) + '% - 6px)'"></div>
            </div>
            <span class="text-2xl font-black text-[#00c389] font-mono mt-3 block" x-text="metrics.completed_today ?? 0"></span>
            <div class="flex items-center gap-1.5 mt-1">
                <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.5 rounded">▲ +14%</span>
                <span class="text-[10px] text-slate-400">today</span>
            </div>
        </div>

        <!-- Metric 6: No-Show Rate -->
        <div class="bg-white dark:bg-[#0f172a] p-4 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-card hover:shadow-card-hover transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">No-Show</span>
                <span class="w-7 h-7 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                    <i data-lucide="user-x" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full mt-3 relative">
                <div class="h-full bg-rose-500 rounded-full" :style="'width: ' + Math.min(100, (metrics.no_show_rate_pct || 0) * 4) + '%'"></div>
                <div class="w-3 h-3 rounded-full bg-rose-500 border-2 border-white dark:border-[#0f172a] shadow-xs absolute -top-0.5" :style="'left: calc(' + Math.min(95, (metrics.no_show_rate_pct || 0) * 4) + '% - 6px)'"></div>
            </div>
            <span class="text-2xl font-black text-rose-600 dark:text-rose-400 font-mono mt-3 block" x-text="(metrics.no_show_rate_pct ?? 0) + '%'"></span>
            <div class="flex items-center gap-1.5 mt-1">
                <span class="text-[10px] font-bold text-slate-400">Unattended</span>
            </div>
        </div>

        <!-- Metric 7: SLA Compliance -->
        <div class="bg-white dark:bg-[#0f172a] p-4 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-card hover:shadow-card-hover transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">SLA Rate</span>
                <span class="w-7 h-7 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full mt-3 relative">
                <div class="h-full bg-emerald-500 rounded-full" :style="'width: ' + (metrics.sla_compliance_pct || 96.5) + '%'"></div>
                <div class="w-3 h-3 rounded-full bg-emerald-500 border-2 border-white dark:border-[#0f172a] shadow-xs absolute -top-0.5" :style="'left: calc(' + (metrics.sla_compliance_pct || 96.5) + '% - 6px)'"></div>
            </div>
            <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono mt-3 block" x-text="(metrics.sla_compliance_pct ?? 96.5) + '%'"></span>
            <div class="flex items-center gap-1.5 mt-1">
                <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.5 rounded">Target met</span>
            </div>
        </div>
    </div>

    <!-- 2. Intelligent Operational Capacity Recommendations (M/M/c Algorithmic) -->
    <div class="bg-gradient-to-r from-amber-50/60 via-emerald-50/30 to-white dark:from-amber-950/20 dark:via-emerald-950/20 dark:to-[#0f172a] border border-amber-200/80 dark:border-amber-900/50 rounded-3xl p-6 sm:p-7 shadow-card interactive-card">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 rounded-full bg-amber-500 text-white font-bold text-[10px] uppercase tracking-wider shadow-xs">
                    Algorithmic Capacity Engine
                </span>
                <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="lightbulb" class="w-5 h-5 text-amber-500"></i>
                    Automated Counter Capacity Recommendations
                </h2>
            </div>
            <span class="text-xs text-slate-500 dark:text-slate-400 hidden sm:inline-flex items-center gap-1">
                <i data-lucide="cpu" class="w-3.5 h-3.5 text-slate-400"></i> Erlang-C Automated Diagnostics
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <template x-for="rec in recommendations" :key="rec.department_id">
                <div class="p-5 rounded-2xl bg-white dark:bg-[#142034] border border-amber-200 dark:border-amber-800/60 shadow-sm flex items-start gap-4 hover:shadow-md transition">
                    <div class="w-11 h-11 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 border border-amber-200/70 dark:border-amber-800/70">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-slate-900 dark:text-white" x-text="rec.department_name + ' (' + rec.department_code + ')'"></span>
                            <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full border shadow-2xs"
                                  :class="rec.severity === 'CRITICAL' ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800'"
                                  x-text="rec.severity">
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1.5 leading-relaxed" x-text="rec.message"></p>
                        <div class="mt-3.5 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                            <span class="text-xs font-bold text-emerald-700 dark:text-emerald-300 flex items-center gap-1.5">
                                <i data-lucide="arrow-right-circle" class="w-4 h-4 text-[#00c389]"></i>
                                <span x-text="'Suggested: ' + rec.actionable"></span>
                            </span>
                            <button @click="addCounter(rec.department_id)" class="px-3.5 py-1.5 rounded-full bg-[#00c389] hover:bg-[#00ab78] text-white text-xs font-bold transition hover:scale-105 active:scale-95 shadow-sm shadow-emerald-500/20">
                                + Add Desk Now
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="recommendations.length === 0">
                <div class="col-span-2 p-5 rounded-2xl bg-white dark:bg-[#142034] border border-emerald-200/80 dark:border-emerald-800/60 text-xs text-slate-700 dark:text-slate-300 flex items-center gap-3.5 shadow-sm">
                    <div class="w-9 h-9 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-200 dark:border-emerald-800">
                        <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <strong class="font-bold text-slate-900 dark:text-white block">Queue Capacity Balanced</strong>
                        <span class="text-slate-500 dark:text-slate-400">All service departments are operating within optimal utilization limits (&rho; &lt; 0.85). Campus service queue flow is fully stable.</span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- 2.5 STOCHASTIC QUEUE SIMULATION & STRESS TESTING LAB (Standout What-If Scenario Modeler) -->
    <div class="bg-white dark:bg-[#0f172a] border border-slate-100 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-card space-y-6 interactive-card">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 gap-3">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-800/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                    <i data-lucide="sliders" class="w-5 h-5"></i>
                </span>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white font-display">M/M/c Stochastic Queue Simulation Lab</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">Interactive What-If Modeler</span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Live parameter sensitivity testing using closed-form Erlang-C equations and capacity forecasting</p>
                </div>
            </div>

            <!-- Preset Scenarios -->
            <div class="flex items-center gap-2">
                <button type="button" @click="triggerRushSimulation()" class="px-3.5 py-1.5 rounded-full bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs hover:scale-105">
                    <i data-lucide="zap" class="w-3.5 h-3.5 text-rose-500"></i>
                    <span>Simulate Exam Rush (+75/hr)</span>
                </button>
                <button type="button" @click="resetSimulation()" class="px-3 py-1.5 rounded-full bg-slate-50 hover:bg-slate-100 dark:bg-[#142034] dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-xs font-bold transition">
                    Reset
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            
            <!-- Left 6 cols: Interactive Sliders -->
            <div class="lg:col-span-6 space-y-5 bg-slate-50/70 dark:bg-[#142034]/70 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80">
                <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Interactive Parameter Tuning</span>
                
                <!-- Slider 1: Arrival Rate (lambda) -->
                <div>
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="font-bold text-slate-700 dark:text-slate-200">Student Arrival Rate (&lambda;)</span>
                        <span class="font-mono font-black text-slate-900 dark:text-white px-2 py-0.5 rounded bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-700 text-xs" x-text="simLambda + ' students/hr'"></span>
                    </div>
                    <input type="range" min="10" max="120" step="2" x-model.number="simLambda"
                           class="w-full accent-[#00c389] cursor-pointer">
                    <div class="flex justify-between text-[10px] text-slate-400 font-mono mt-1">
                        <span>10 (Quiet)</span>
                        <span>50 (Normal)</span>
                        <span>120 (Peak Rush)</span>
                    </div>
                </div>

                <!-- Slider 2: Active Desks (c) -->
                <div>
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="font-bold text-slate-700 dark:text-slate-200">Active Service Counters (c)</span>
                        <span class="font-mono font-black text-slate-900 dark:text-white px-2 py-0.5 rounded bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-700 text-xs" x-text="simCounters + ' desks'"></span>
                    </div>
                    <input type="range" min="1" max="8" step="1" x-model.number="simCounters"
                           class="w-full accent-[#00c389] cursor-pointer">
                    <div class="flex justify-between text-[10px] text-slate-400 font-mono mt-1">
                        <span>1 Counter</span>
                        <span>4 Counters</span>
                        <span>8 Counters</span>
                    </div>
                </div>

                <!-- Slider 3: Handling Duration (mu^-1) -->
                <div>
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="font-bold text-slate-700 dark:text-slate-200">Average Handling Duration (&mu;⁻¹)</span>
                        <span class="font-mono font-black text-slate-900 dark:text-white px-2 py-0.5 rounded bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-700 text-xs" x-text="simDuration + ' min/student'"></span>
                    </div>
                    <input type="range" min="2" max="15" step="0.5" x-model.number="simDuration"
                           class="w-full accent-[#00c389] cursor-pointer">
                    <div class="flex justify-between text-[10px] text-slate-400 font-mono mt-1">
                        <span>2m (Simple Attest)</span>
                        <span>6m (Clearance)</span>
                        <span>15m (Dispute)</span>
                    </div>
                </div>
            </div>

            <!-- Right 6 cols: Live Mathematical Output & Saturation Meter -->
            <div class="lg:col-span-6 space-y-4">
                
                <!-- Telemetry Metrics Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700">
                        <span class="text-[9px] font-mono font-bold uppercase text-slate-400 block">Offered Load (a)</span>
                        <span class="text-xl font-black font-mono text-slate-900 dark:text-white mt-1 block" x-text="simTrafficIntensity.toFixed(2)"></span>
                        <span class="text-[9px] text-slate-400 font-mono">Erlangs</span>
                    </div>

                    <div class="p-3 rounded-2xl border transition"
                         :class="{
                             'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-300': simUtilization < 0.70,
                             'bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-300': simUtilization >= 0.70 && simUtilization < 0.85,
                             'bg-rose-50 dark:bg-rose-950/40 border-rose-300 dark:border-rose-800 text-rose-900 dark:text-rose-300 animate-pulse': simUtilization >= 0.85
                         }">
                        <span class="text-[9px] font-mono font-bold uppercase block opacity-70">Utilization (&rho;)</span>
                        <span class="text-xl font-black font-mono mt-1 block" x-text="(simUtilization * 100).toFixed(0) + '%'"></span>
                        <span class="text-[9px] font-mono" x-text="simUtilization >= 1.0 ? 'UNSTABLE' : (simUtilization >= 0.85 ? 'CONGESTED' : 'BALANCED')"></span>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700">
                        <span class="text-[9px] font-mono font-bold uppercase text-slate-400 block">Delay Prob C(c,a)</span>
                        <span class="text-xl font-black font-mono text-indigo-600 dark:text-indigo-400 mt-1 block" x-text="simErlangCDelay + '%'"></span>
                        <span class="text-[9px] text-slate-400 font-mono">Chance of wait</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700">
                        <span class="text-[9px] font-mono font-bold uppercase text-slate-400 block">Est. Queue Wait</span>
                        <span class="text-xl font-black font-mono text-[#00c389] mt-1 block" x-text="simWaitTime + 'm'"></span>
                        <span class="text-[9px] text-slate-400 font-mono">Wq minutes</span>
                    </div>
                </div>

                <!-- Diagnostic Engine Alert Box -->
                <div class="p-4 rounded-2xl border text-xs space-y-1.5 transition"
                     :class="{
                         'bg-emerald-50/70 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-300': simUtilization < 0.85,
                         'bg-rose-50 dark:bg-rose-950/40 border-rose-300 dark:border-rose-800 text-rose-900 dark:text-rose-300': simUtilization >= 0.85
                     }">
                    <div class="flex items-center justify-between font-bold">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="cpu" class="w-4 h-4"></i>
                            <span x-text="simUtilization >= 1.0 ? 'CRITICAL SATURATION WARNING' : (simUtilization >= 0.85 ? 'CAPACITY SHORTAGE ALERT' : 'QUEUE FLOW OPTIMAL')"></span>
                        </span>
                        <span class="font-mono text-[10px] px-2 py-0.5 rounded-full bg-white/80 dark:bg-slate-900/80 dark:text-white border border-slate-200 dark:border-slate-700"
                              x-text="'Optimal Desks: ' + simRequiredCounters"></span>
                    </div>
                    <p class="leading-relaxed text-[11px]"
                       x-text="simUtilization >= 1.0 ? 'CRITICAL: Arrival rate exceeds maximum server capacity (ρ ≥ 1.0). Queue length will explode indefinitely! Add at least ' + (simRequiredCounters - simCounters) + ' desks immediately.' : (simUtilization >= 0.85 ? 'HIGH CONGESTION: Server utilization is above safe 85% threshold. Erlang-C predicts exponential queue explosion. Provision ' + simRequiredCounters + ' total desks to maintain sub-5 minute wait time.' : 'STABLE: The current ' + simCounters + ' counter configuration comfortably handles ' + simLambda + ' students/hr with average wait of only ' + simWaitTime + ' minutes.')">
                    </p>
                </div>

            </div>
        </div>
    </div>

    <!-- 3. Hourly Analytics Charts Row (Clean Light HOMEAGE Style) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Chart 1: Hourly Arrivals vs Served -->
        <div class="bg-white dark:bg-[#0f172a] border border-slate-100 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-card space-y-4 interactive-card">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Hourly Throughput & Arrivals</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Total student arrivals vs completed services per operating hour</p>
                </div>
                <span class="text-xs font-mono font-bold text-[#00c389] bg-[#00c389]/10 px-3 py-1 rounded-full border border-[#00c389]/20">Peak Hours</span>
            </div>
            <div class="h-64 relative">
                <canvas id="throughputChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Average Wait Time Trend (Spline Wave) -->
        <div class="bg-white dark:bg-[#0f172a] border border-slate-100 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-card space-y-4 interactive-card">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Average Wait Time Trend (Minutes)</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Dynamic queue delay across operating hours with Erlang-C projection</p>
                </div>
                <span class="text-xs font-mono font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1 rounded-full border border-emerald-200 dark:border-emerald-800">SLA: &lt; 15 min</span>
            </div>
            <div class="h-64 relative">
                <canvas id="waitTimeChart"></canvas>
            </div>
        </div>
    </div>

    <!-- 4. Peak Congestion Heatmap Matrix -->
    <div class="bg-white dark:bg-[#0f172a] border border-slate-100 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-card space-y-4 interactive-card">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 gap-3">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Department Queue Congestion Heatmap</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Hourly counter utilization percentages across active university shifts (08:00–16:00)</p>
            </div>
            <div class="flex items-center gap-3 text-xs font-semibold text-slate-600 dark:text-slate-300">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-emerald-100 dark:bg-emerald-900/60 border border-emerald-300 dark:border-emerald-700"></span> &lt; 65% Low</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-amber-100 dark:bg-amber-900/60 border border-amber-300 dark:border-amber-700"></span> 65-84% Moderate</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-rose-100 dark:bg-rose-900/60 border border-rose-300 dark:border-rose-700"></span> &ge; 85% Congested</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-center border-collapse">
                <thead>
                    <tr class="text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800">
                        <th class="py-3 text-left font-bold pl-3">Department</th>
                        <th class="py-3 font-mono font-bold">08:00</th>
                        <th class="py-3 font-mono font-bold">09:00</th>
                        <th class="py-3 font-mono font-bold">10:00</th>
                        <th class="py-3 font-mono font-bold">11:00</th>
                        <th class="py-3 font-mono font-bold">12:00</th>
                        <th class="py-3 font-mono font-bold">13:00</th>
                        <th class="py-3 font-mono font-bold">14:00</th>
                        <th class="py-3 font-mono font-bold">15:00</th>
                        <th class="py-3 font-mono font-bold">16:00</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-mono">
                    <template x-for="dept in heatmap" :key="dept.department_id">
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-[#142034]/60 transition">
                            <td class="py-3.5 text-left font-bold text-slate-900 dark:text-white pl-3 font-sans" x-text="dept.department_name + ' (' + dept.department_code + ')'"></td>
                            <template x-for="h in dept.hours" :key="h.hour">
                                <td class="py-3.5 px-1">
                                    <span class="inline-block px-2.5 py-1 rounded-lg text-[11px] font-bold border shadow-2xs"
                                          :class="{
                                              'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800': h.intensity === 'LOW',
                                              'bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800': h.intensity === 'MEDIUM',
                                              'bg-rose-50 dark:bg-rose-950/50 text-rose-800 dark:text-rose-300 font-black border-rose-200 dark:border-rose-800': h.intensity === 'HIGH'
                                          }"
                                          x-text="h.utilization_pct + '%'">
                                    </span>
                                </td>
                            </template>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. Interactive Department & Counter Administration -->
    <div class="bg-white dark:bg-[#0f172a] border border-slate-100 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-card space-y-5 interactive-card">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 gap-3">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Department Desks & Counter Management</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Control active desks, toggle department opening status, and provision capacity live</p>
            </div>
            <span class="text-xs font-mono font-bold text-[#00c389] bg-[#00c389]/10 px-3.5 py-1.5 rounded-full border border-[#00c389]/20 self-start sm:self-auto">
                {{ $departments->count() }} Registered Departments
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($departments as $d)
                @php
                    $waitingTickets = $d->queueTickets()->where('status', 'WAITING')->get();
                @endphp
                <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-[#142034]/50 space-y-4 flex flex-col justify-between hover:border-slate-200 dark:hover:border-slate-700 hover:shadow-sm transition">
                    <div>
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="px-2.5 py-1 rounded-md font-mono font-bold text-xs bg-white dark:bg-[#0f172a] text-[#00c389] border border-slate-200 dark:border-slate-700 shadow-2xs">{{ $d->code }}</span>
                                <h3 class="font-bold text-base text-slate-900 dark:text-white mt-2">{{ $d->name }}</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $d->location ?? 'Main Floor' }}</p>
                            </div>
                            <button @click="toggleDeptStatus({{ $d->id }})" class="px-3.5 py-1 rounded-full text-xs font-bold border transition shadow-2xs"
                                    :class="{{ $d->is_active ? 'true' : 'false' }} ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 dark:hover:bg-emerald-900/40' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700'">
                                {{ $d->is_active ? 'OPEN' : 'CLOSED' }}
                            </button>
                        </div>

                        <!-- Counters List -->
                        <div class="mt-4 pt-3.5 border-t border-slate-200/70 dark:border-slate-700/70">
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Counters ({{ $d->counters->count() }}):</span>
                                <button @click="addCounter({{ $d->id }})" class="text-xs font-bold text-[#00c389] hover:text-[#00ab78] flex items-center gap-1 transition">
                                    <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> Add Desk
                                </button>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($d->counters as $counter)
                                    <button @click="toggleCounter({{ $counter->id }})"
                                            title="Click to toggle status"
                                            class="px-2.5 py-1 rounded-lg text-[10px] font-bold font-mono transition border {{ $counter->status === 'AVAILABLE' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 dark:hover:bg-emerald-900/40' : ($counter->status === 'BUSY' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800 hover:bg-amber-100 dark:hover:bg-amber-900/40' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700') }}">
                                        {{ $counter->name }}: {{ $counter->status }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-200/70 dark:border-slate-700/70 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                        <span>Waiting: <strong class="text-emerald-700 dark:text-emerald-400 font-mono font-bold">{{ $waitingTickets->count() }}</strong></span>
                        <span>Avg Handling: <strong class="text-slate-900 dark:text-white font-mono">~{{ $d->avg_handling_time_min }}m</strong></span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- New Department Modal -->
    <div x-show="openNewDeptModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div @click.away="openNewDeptModal = false" class="bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 sm:p-7 space-y-5 shadow-2xl text-slate-900 dark:text-white">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-[#00c389]/10 text-[#00c389] flex items-center justify-center border border-[#00c389]/20">
                    <i data-lucide="building" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Provision New Department</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Add an operational department and default counter desk.</p>
                </div>
            </div>

            <form @submit.prevent="submitNewDept()" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Department Code (3-4 chars)</label>
                    <input type="text" x-model="newDept.code" placeholder="e.g. ADM" required class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-mono uppercase focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Department Name</label>
                    <input type="text" x-model="newDept.name" placeholder="e.g. Admissions Office" required class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Location / Office Desk</label>
                    <input type="text" x-model="newDept.location" placeholder="e.g. Level 1, Room 101" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Benchmark Handling Time (Minutes)</label>
                    <input type="number" x-model="newDept.avg_handling_time_min" value="5" min="1" max="60" required class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389]">
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="openNewDeptModal = false" class="px-4 py-2.5 rounded-full border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 rounded-full bg-[#00c389] hover:bg-[#00ab78] text-white text-xs font-bold shadow-md shadow-emerald-500/20 transition hover:scale-105 active:scale-95">Create Department</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function adminDashboard() {
    return {
        metrics: {},
        recommendations: @json($recommendations),
        heatmap: [],
        hourly: [],
        refreshing: false,
        openNewDeptModal: false,
        newDept: { code: '', name: '', location: '', avg_handling_time_min: 5 },
        throughputChart: null,
        waitTimeChart: null,

        // Stochastic Simulation State
        simLambda: 36,
        simCounters: 4,
        simDuration: 5,

        get simTrafficIntensity() {
            const muPerHour = 60 / (this.simDuration || 5);
            return (this.simLambda || 36) / muPerHour;
        },

        get simUtilization() {
            return this.simTrafficIntensity / (this.simCounters || 1);
        },

        get simRequiredCounters() {
            return Math.max(1, Math.ceil(this.simTrafficIntensity / 0.70));
        },

        get simErlangCDelay() {
            const a = this.simTrafficIntensity;
            const c = this.simCounters || 1;
            const rho = this.simUtilization;
            if (rho >= 1.0) return 99.9;

            // Compute P0
            let sum = 0;
            let term = 1;
            for (let k = 0; k < c; k++) {
                if (k > 0) term = term * (a / k);
                sum += term;
            }
            term = term * (a / c);
            const p0 = 1 / (sum + term * (1 / (1 - rho)));
            const delayProb = (term * (1 / (1 - rho))) * p0;
            return Math.min(99.9, Math.max(0.1, delayProb * 100)).toFixed(1);
        },

        get simWaitTime() {
            const a = this.simTrafficIntensity;
            const c = this.simCounters || 1;
            const rho = this.simUtilization;
            const muPerHour = 60 / (this.simDuration || 5);
            if (rho >= 1.0) return '∞ (Explosive)';

            const delayProbDecimal = parseFloat(this.simErlangCDelay) / 100;
            const wqHours = delayProbDecimal / (c * muPerHour - this.simLambda);
            const wqMin = wqHours * 60;
            return Math.max(0.2, wqMin).toFixed(1);
        },

        triggerRushSimulation() {
            this.simLambda = 85;
            this.simCounters = 3;
            this.simDuration = 6.5;
            if (window.playTactileSound) window.playTactileSound('chime');
            if (window.showToast) window.showToast('Peak Exam Rush Simulated! Capacity engine alerting...', 'error');
            this.$nextTick(() => lucide.createIcons());
        },

        resetSimulation() {
            this.simLambda = 36;
            this.simCounters = 4;
            this.simDuration = 5;
            if (window.playTactileSound) window.playTactileSound('click');
            if (window.showToast) window.showToast('Queue parameters reset to normal baseline.', 'success');
            this.$nextTick(() => lucide.createIcons());
        },

        async init() {
            lucide.createIcons();
            await this.refreshData();
            this.initCharts();

            // Auto-refresh every 5s
            setInterval(() => {
                this.refreshData();
            }, 5000);
        },

        async refreshData() {
            this.refreshing = true;
            try {
                const [mRes, hRes, heatRes, recRes] = await Promise.all([
                    fetch('/api/admin/metrics'),
                    fetch('/api/admin/hourly'),
                    fetch('/api/admin/heatmap'),
                    fetch('/api/admin/recommendations')
                ]);

                if (mRes.ok) this.metrics = (await mRes.json()).data;
                if (hRes.ok) {
                    this.hourly = (await hRes.json()).data;
                    this.updateCharts();
                }
                if (heatRes.ok) this.heatmap = (await heatRes.json()).data;
                if (recRes.ok) this.recommendations = (await recRes.json()).data;

                this.$nextTick(() => lucide.createIcons());
            } catch (err) {
                console.error("Admin metrics sync error:", err);
            } finally {
                setTimeout(() => { this.refreshing = false; }, 400);
            }
        },

        async toggleDeptStatus(id) {
            try {
                const res = await fetch(`/api/admin/departments/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const json = await res.json();
                if (json.success) {
                    window.location.reload();
                }
            } catch (err) {
                alert("Error toggling department.");
            }
        },

        async addCounter(deptId) {
            try {
                const res = await fetch('/api/admin/counters', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ department_id: deptId })
                });
                const json = await res.json();
                if (json.success) {
                    if (window.showToast) window.showToast(json.message, "success");
                    this.refreshData();
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    if (window.showToast) window.showToast(json.message || "Failed to add counter.", "error");
                }
            } catch (err) {
                if (window.showToast) window.showToast("Error adding counter.", "error");
            }
        },

        async toggleCounter(counterId) {
            try {
                const res = await fetch(`/api/admin/counters/${counterId}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const json = await res.json();
                if (json.success) {
                    if (window.showToast) window.showToast(json.message, "info");
                    setTimeout(() => window.location.reload(), 500);
                }
            } catch (err) {
                if (window.showToast) window.showToast("Error updating counter.", "error");
            }
        },

        async submitNewDept() {
            try {
                const res = await fetch('/api/admin/departments', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.newDept)
                });
                const json = await res.json();
                if (json.success) {
                    this.openNewDeptModal = false;
                    if (window.showToast) window.showToast("Department created successfully!", "success");
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    if (window.showToast) window.showToast(json.message || "Failed to create department.", "error");
                }
            } catch (err) {
                if (window.showToast) window.showToast("Error creating department.", "error");
            }
        },

        async resetQueues() {
            if (!confirm("Are you sure you want to cancel and clear all waiting queue tickets?")) return;
            try {
                const res = await fetch('/api/admin/reset-queues', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const json = await res.json();
                if (json.success) {
                    if (window.showToast) window.showToast(json.message, "info");
                    this.refreshData();
                    setTimeout(() => window.location.reload(), 600);
                }
            } catch (err) {
                if (window.showToast) window.showToast("Error resetting queues.", "error");
            }
        },

        initCharts() {
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)';
            const tickColor = isDark ? '#94a3b8' : '#64748b';
            const labelColor = isDark ? '#cbd5e1' : '#475569';

            const ctx1 = document.getElementById('throughputChart')?.getContext('2d');
            const ctx2 = document.getElementById('waitTimeChart')?.getContext('2d');

            if (ctx1) {
                this.throughputChart = new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: [],
                        datasets: [
                            { label: 'Arrivals', data: [], backgroundColor: isDark ? '#475569' : '#64748b', borderRadius: 8 },
                            { label: 'Served', data: [], backgroundColor: '#00c389', borderRadius: 8 }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { boxWidth: 12, color: labelColor, font: { family: 'Plus Jakarta Sans, sans-serif', weight: '600' } }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { color: tickColor, font: { family: 'Plus Jakarta Sans, sans-serif' } }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: tickColor, font: { family: 'Plus Jakarta Sans, sans-serif' } }
                            }
                        }
                    }
                });
            }

            if (ctx2) {
                this.waitTimeChart = new Chart(ctx2, {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [
                            {
                                label: 'Avg Wait (Minutes)',
                                data: [],
                                borderColor: '#00c389',
                                backgroundColor: isDark ? 'rgba(0, 195, 137, 0.2)' : 'rgba(0, 195, 137, 0.12)',
                                borderWidth: 3,
                                tension: 0.4,
                                fill: true,
                                pointRadius: 4,
                                pointBackgroundColor: '#00c389',
                                pointBorderColor: isDark ? '#0f172a' : '#ffffff',
                                pointBorderWidth: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { color: tickColor, font: { family: 'Plus Jakarta Sans, sans-serif' } }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: tickColor, font: { family: 'Plus Jakarta Sans, sans-serif' } }
                            }
                        }
                    }
                });
            }
        },

        updateCharts() {
            if (!this.hourly || this.hourly.length === 0) return;

            const labels = this.hourly.map(r => r.hour);
            const arrivals = this.hourly.map(r => r.arrivals);
            const served = this.hourly.map(r => r.served);
            const wait = this.hourly.map(r => r.avg_wait_min);

            if (this.throughputChart) {
                this.throughputChart.data.labels = labels;
                this.throughputChart.data.datasets[0].data = arrivals;
                this.throughputChart.data.datasets[1].data = served;
                this.throughputChart.update();
            }

            if (this.waitTimeChart) {
                this.waitTimeChart.data.labels = labels;
                this.waitTimeChart.data.datasets[0].data = wait;
                this.waitTimeChart.update();
            }
        }
    }
}
</script>
@endpush
@endsection
