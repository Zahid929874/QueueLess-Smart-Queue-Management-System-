@extends('layouts.app', ['title' => 'Campus Smart Queue Management'])

@section('content')
<div x-data="homeDashboard()" x-init="init()" class="space-y-6">

    <!-- 1. TOP ROW: 4 HORIZONTAL KPI METRIC CARDS WITH SLIDER TRACKS (Matching Reference Row 1) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        
        <!-- KPI Card 1: Students in Queue -->
        <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-card interactive-card flex flex-col justify-between transition-colors duration-300">
            <div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-mint-50 dark:bg-mint-950/50 border border-mint-100 dark:border-mint-800/60 flex items-center justify-center text-[#00c389]">
                            <i data-lucide="users" class="w-4 h-4"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Waiting in Queue</span>
                    </div>
                    <button class="text-slate-300 dark:text-slate-600 hover:text-slate-600 dark:hover:text-slate-300 transition" title="Options">
                        <i data-lucide="more-vertical" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Slider Indicator Bar -->
                <div class="mt-4 slider-track">
                    <div class="slider-fill" style="width: 35%;"></div>
                    <div class="slider-knob" style="left: 35%;"></div>
                </div>
            </div>

            <div class="mt-4 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-black font-display text-slate-900 dark:text-white tracking-tight" x-text="metrics.waiting_students ?? 24"></span>
                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-800/50">
                    <i data-lucide="trending-down" class="w-3 h-3"></i> -1.6% <span class="text-slate-400 dark:text-slate-500 font-normal">Since last hour</span>
                </span>
            </div>
        </div>

        <!-- KPI Card 2: Active Desks Staffed -->
        <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-card interactive-card flex flex-col justify-between transition-colors duration-300">
            <div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-mint-50 dark:bg-mint-950/50 border border-mint-100 dark:border-mint-800/60 flex items-center justify-center text-[#00c389]">
                            <i data-lucide="headset" class="w-4 h-4"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Active Service Desks</span>
                    </div>
                    <button class="text-slate-300 dark:text-slate-600 hover:text-slate-600 dark:hover:text-slate-300 transition" title="Options">
                        <i data-lucide="more-vertical" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Slider Indicator Bar -->
                <div class="mt-4 slider-track">
                    <div class="slider-fill" style="width: 70%;"></div>
                    <div class="slider-knob" style="left: 70%;"></div>
                </div>
            </div>

            <div class="mt-4 flex items-baseline justify-between">
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl sm:text-3xl font-black font-display text-slate-900 dark:text-white tracking-tight" x-text="metrics.active_counters ?? 14"></span>
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500 font-mono">/ 20</span>
                </div>
                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/50">
                    <i data-lucide="trending-up" class="w-3 h-3"></i> +1.8% <span class="text-slate-400 dark:text-slate-500 font-normal">Since morning</span>
                </span>
            </div>
        </div>

        <!-- KPI Card 3: Total Students Served -->
        <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-card interactive-card flex flex-col justify-between transition-colors duration-300">
            <div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-mint-50 dark:bg-mint-950/50 border border-mint-100 dark:border-mint-800/60 flex items-center justify-center text-[#00c389]">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Total Students Served</span>
                    </div>
                    <button class="text-slate-300 dark:text-slate-600 hover:text-slate-600 dark:hover:text-slate-300 transition" title="Options">
                        <i data-lucide="more-vertical" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Slider Indicator Bar -->
                <div class="mt-4 slider-track">
                    <div class="slider-fill" style="width: 82%;"></div>
                    <div class="slider-knob" style="left: 82%;"></div>
                </div>
            </div>

            <div class="mt-4 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-black font-display text-slate-900 dark:text-white tracking-tight" x-text="metrics.completed_today ?? 482"></span>
                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/50">
                    <i data-lucide="trending-up" class="w-3 h-3"></i> +14.8% <span class="text-slate-400 dark:text-slate-500 font-normal">Since last week</span>
                </span>
            </div>
        </div>

        <!-- KPI Card 4: Average Wait Duration -->
        <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-card interactive-card flex flex-col justify-between transition-colors duration-300">
            <div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-mint-50 dark:bg-mint-950/50 border border-mint-100 dark:border-mint-800/60 flex items-center justify-center text-[#00c389]">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Average Wait Time</span>
                    </div>
                    <button class="text-slate-300 dark:text-slate-600 hover:text-slate-600 dark:hover:text-slate-300 transition" title="Options">
                        <i data-lucide="more-vertical" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Slider Indicator Bar -->
                <div class="mt-4 slider-track">
                    <div class="slider-fill" style="width: 25%;"></div>
                    <div class="slider-knob" style="left: 25%;"></div>
                </div>
            </div>

            <div class="mt-4 flex items-baseline justify-between">
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl sm:text-3xl font-black font-display text-slate-900 dark:text-white tracking-tight" x-text="(metrics.avg_wait_time_min ?? 4.5) + 'm'"></span>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">M/M/c</span>
                </div>
                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/50">
                    <i data-lucide="check" class="w-3 h-3"></i> -65% <span class="text-slate-400 dark:text-slate-500 font-normal">vs lines</span>
                </span>
            </div>
        </div>

    </div>

    <!-- 2. MIDDLE ROW: 3 ANALYTICAL DATA CARDS (Matching Reference Row 2) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        <!-- Column 1: Queue Volume by Department (Segmented Capsule Bar Chart) -->
        <div class="lg:col-span-5 bg-white dark:bg-[#0f172a] rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-card interactive-card flex flex-col justify-between relative overflow-hidden transition-colors duration-300">
            <div class="flex items-center justify-between pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Queue Flow by Office</h3>
                <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-[#142034] border border-slate-100 dark:border-slate-700/80 px-2.5 py-1 rounded-lg font-medium">
                    <span>Live Today</span>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                </div>
            </div>

            <!-- Segmented Capsule Bar Visual (Faithfully Matching the HOMEAGE Capsule Bar Component) -->
            <div class="pt-6 pb-2 relative">
                <!-- Floating Active Tooltip matching reference -->
                <div class="absolute top-1 left-[45%] -translate-x-1/2 bg-white dark:bg-[#162238] rounded-xl shadow-lg border border-slate-100 dark:border-slate-700 p-2 text-[10px] space-y-0.5 z-20 pointer-events-none transition-all">
                    <span class="text-slate-400 dark:text-slate-500 block font-bold">11:00 AM (Peak)</span>
                    <span class="flex items-center gap-1.5 font-bold text-slate-800 dark:text-slate-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#00c389]"></span> Accounts: 42
                    </span>
                    <span class="flex items-center gap-1.5 font-bold text-slate-800 dark:text-slate-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-500"></span> Registrar: 28
                    </span>
                </div>

                <!-- Grid Horizontal Lines -->
                <div class="h-44 w-full flex flex-col justify-between relative border-b border-dashed border-slate-200 dark:border-slate-800 pb-1">
                    <div class="w-full border-t border-dashed border-slate-100 dark:border-slate-800/60"></div>
                    <div class="w-full border-t border-dashed border-slate-100 dark:border-slate-800/60"></div>
                    <div class="w-full border-t border-dashed border-slate-100 dark:border-slate-800/60"></div>
                    <div class="w-full border-t border-dashed border-slate-100 dark:border-slate-800/60"></div>

                    <!-- Bars Flex Container -->
                    <div class="absolute inset-x-2 bottom-1 top-2 flex items-end justify-between gap-2 px-1">
                        <!-- Bar 1: 08:00 -->
                        <div class="flex-1 flex flex-col items-center gap-1 group cursor-pointer">
                            <div class="w-full max-w-[28px] h-20 rounded-full bg-mint-100 dark:bg-mint-950/50 group-hover:bg-mint-200 dark:group-hover:bg-mint-900/50 transition-all flex flex-col justify-end p-0.5">
                                <div class="w-full h-12 rounded-full bg-[#00c389]/60"></div>
                            </div>
                        </div>

                        <!-- Bar 2: 09:00 -->
                        <div class="flex-1 flex flex-col items-center gap-1 group cursor-pointer">
                            <div class="w-full max-w-[28px] h-28 rounded-full striped-bar-pattern border border-[#00c389]/20 group-hover:border-[#00c389] transition-all flex flex-col justify-end p-0.5">
                                <div class="w-full h-16 rounded-full bg-[#00c389]/70"></div>
                            </div>
                        </div>

                        <!-- Bar 3: 10:00 -->
                        <div class="flex-1 flex flex-col items-center gap-1 group cursor-pointer">
                            <div class="w-full max-w-[28px] h-32 rounded-full bg-mint-100 dark:bg-mint-950/50 group-hover:bg-mint-200 dark:group-hover:bg-mint-900/50 transition-all flex flex-col justify-end p-0.5">
                                <div class="w-full h-20 rounded-full bg-[#00c389]/80"></div>
                            </div>
                        </div>

                        <!-- Bar 4: 11:00 (Peak Active Prominent Dark Emerald Bar matching reference) -->
                        <div class="flex-1 flex flex-col items-center gap-1 group cursor-pointer transform -translate-y-1">
                            <div class="w-full max-w-[32px] h-38 rounded-full bg-[#059669] shadow-md shadow-mint flex flex-col justify-end p-1 transition-all group-hover:scale-105">
                                <div class="w-full h-24 rounded-full bg-[#00c389] border border-white/40"></div>
                            </div>
                        </div>

                        <!-- Bar 5: 12:00 -->
                        <div class="flex-1 flex flex-col items-center gap-1 group cursor-pointer">
                            <div class="w-full max-w-[28px] h-26 rounded-full striped-bar-pattern border border-[#00c389]/20 group-hover:border-[#00c389] transition-all flex flex-col justify-end p-0.5">
                                <div class="w-full h-14 rounded-full bg-[#00c389]/70"></div>
                            </div>
                        </div>

                        <!-- Bar 6: 02:00 -->
                        <div class="flex-1 flex flex-col items-center gap-1 group cursor-pointer">
                            <div class="w-full max-w-[28px] h-24 rounded-full bg-mint-100 dark:bg-mint-950/50 group-hover:bg-mint-200 dark:group-hover:bg-mint-900/50 transition-all flex flex-col justify-end p-0.5">
                                <div class="w-full h-14 rounded-full bg-[#00c389]/60"></div>
                            </div>
                        </div>

                        <!-- Bar 7: 04:00 -->
                        <div class="flex-1 flex flex-col items-center gap-1 group cursor-pointer">
                            <div class="w-full max-w-[28px] h-18 rounded-full bg-mint-100 dark:bg-mint-950/50 group-hover:bg-mint-200 dark:group-hover:bg-mint-900/50 transition-all flex flex-col justify-end p-0.5">
                                <div class="w-full h-10 rounded-full bg-[#00c389]/50"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- X-Axis Labels -->
                <div class="flex items-center justify-between text-[10px] text-slate-400 dark:text-slate-500 font-medium px-2 pt-2">
                    <span>8 AM</span>
                    <span>9 AM</span>
                    <span>10 AM</span>
                    <span class="text-[#059669] dark:text-[#00c389] font-bold">11 AM</span>
                    <span>12 PM</span>
                    <span>2 PM</span>
                    <span>4 PM</span>
                </div>
            </div>
        </div>

        <!-- Column 2: Service Progress & Wait Velocity (Smooth Spline Wave Curve) -->
        <div class="lg:col-span-4 bg-white dark:bg-[#0f172a] rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-card interactive-card flex flex-col justify-between transition-colors duration-300">
            <div class="flex items-center justify-between pb-2">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Queue Velocity</h3>
                <div class="flex items-center gap-1 text-xs text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-[#142034] border border-slate-100 dark:border-slate-700/80 px-2.5 py-1 rounded-lg font-medium">
                    <span>Today</span>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                </div>
            </div>

            <!-- Big Metric Percentage (Matching Reference "Project Progress: 60%") -->
            <div class="flex items-baseline gap-2 pt-1">
                <span class="text-3xl font-black font-display text-slate-900 dark:text-white">84%</span>
                <span class="inline-flex items-center gap-0.5 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="trending-up" class="w-3.5 h-3.5"></i> +1.6% <span class="text-slate-400 dark:text-slate-500 font-normal">SLA rate</span>
                </span>
            </div>

            <!-- Smooth Spline Wave Curve (SVG matching reference spline wave) -->
            <div class="pt-4 pb-2">
                <svg class="w-full h-32 overflow-visible" viewBox="0 0 300 100" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="velocityGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#00c389" stop-opacity="0.25"/>
                            <stop offset="100%" stop-color="#00c389" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>
                    <!-- Area Fill -->
                    <path d="M 0,70 Q 30,85 60,60 T 120,45 T 180,65 T 240,25 T 300,40 L 300,100 L 0,100 Z" fill="url(#velocityGradient)"></path>
                    <!-- Spline Line -->
                    <path d="M 0,70 Q 30,85 60,60 T 120,45 T 180,65 T 240,25 T 300,40" fill="none" stroke="#00c389" stroke-width="2.5" stroke-linecap="round"></path>
                    <!-- Nodes -->
                    <circle cx="120" cy="45" r="3.5" fill="#ffffff" stroke="#00c389" stroke-width="2"></circle>
                    <circle cx="240" cy="25" r="4.5" fill="#ffffff" stroke="#00c389" stroke-width="2.5"></circle>
                </svg>

                <div class="flex items-center justify-between text-[10px] text-slate-400 dark:text-slate-500 font-medium pt-2">
                    <span>Morning</span>
                    <span>Mid-Day</span>
                    <span class="text-[#00c389] font-bold">Current</span>
                    <span>Closing</span>
                </div>
            </div>
        </div>

        <!-- Column 3: Status Analysis (Concentric Multi-Ring Target Funnel) -->
        <div class="lg:col-span-3 bg-white dark:bg-[#0f172a] rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-card interactive-card flex flex-col justify-between transition-colors duration-300">
            <div class="flex items-center justify-between pb-2">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Status Analysis</h3>
                <div class="flex items-center gap-1 text-xs text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-[#142034] border border-slate-100 dark:border-slate-700/80 px-2 py-1 rounded-lg font-medium">
                    <span>All</span>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                </div>
            </div>

            <!-- Concentric Target Circles (Faithfully matching the nested green rings in the HOMEAGE image) -->
            <div class="relative py-4 flex items-center justify-center">
                <!-- Outer Ring (Accepted: 52) -->
                <div class="w-44 h-44 rounded-full bg-mint-50/70 dark:bg-mint-950/20 border border-mint-100 dark:border-mint-800/40 flex items-center justify-center transition-all duration-300 hover:scale-105 cursor-pointer relative group">
                    <span class="absolute top-2 text-[10px] font-bold text-mint-700 dark:text-mint-400">Served: 482</span>

                    <!-- Middle Ring (Equities: 38) -->
                    <div class="w-32 h-32 rounded-full bg-mint-100/80 dark:bg-mint-950/40 border border-mint-200 dark:border-mint-800/60 flex items-center justify-center transition-all duration-300 hover:scale-105 relative">
                        <span class="absolute top-2 text-[10px] font-bold text-mint-800 dark:text-mint-300">In-Desk: 18</span>

                        <!-- Inner Ring (Rejected: 4) -->
                        <div class="w-22 h-22 rounded-full bg-[#10b981]/70 border border-emerald-400 flex items-center justify-center transition-all duration-300 relative">
                            <span class="absolute top-1 text-[9px] font-bold text-white">Wait: 24</span>

                            <!-- Core Center Circle (Pending: 23) -->
                            <div class="w-13 h-13 rounded-full bg-[#00c389] text-white flex flex-col items-center justify-center font-bold shadow-md shadow-mint animate-ring-pulse">
                                <span class="text-[9px] uppercase tracking-wider text-white/80 leading-none">Ready</span>
                                <span class="text-sm font-black font-display leading-tight">98%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ring Legend Chips -->
            <div class="grid grid-cols-2 gap-1.5 text-[11px] pt-1">
                <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400 font-medium">
                    <span class="w-2 h-2 rounded-full bg-[#00c389]"></span> Completed (82%)
                </span>
                <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span> In Consultation
                </span>
            </div>
        </div>

    </div>

    <!-- 3. BOTTOM ROW: SPLIT GRID (Campus Service Desks + Live Dispatch Feed) (Matching Reference Row 3) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        <!-- Left 8 cols: Campus Service Desks & Virtual Dispenser (Matching Reference "Your Properties") -->
        <div class="lg:col-span-8 bg-white dark:bg-[#0f172a] rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-card interactive-card space-y-5 transition-colors duration-300">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white font-display">Campus Service Desks</h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Select an office to claim an instantaneous digital queue token</p>
                </div>

                <!-- View Switcher & Search Controls -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Segmented View Toggle (Grid vs Spatial Twin) -->
                    <div class="flex items-center p-0.5 rounded-full bg-slate-100 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-xs font-bold">
                        <button type="button" @click="campusView = 'grid'; if (window.playTactileSound) window.playTactileSound('click')"
                                :class="campusView === 'grid' ? 'bg-white dark:bg-[#1e293b] text-slate-900 dark:text-white shadow-2xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                                class="px-3 py-1 rounded-full transition flex items-center gap-1.5">
                            <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                            <span>Grid View</span>
                        </button>
                        <button type="button" @click="campusView = 'spatial_map'; if (window.playTactileSound) window.playTactileSound('click')"
                                :class="campusView === 'spatial_map' ? 'bg-[#00c389] text-white shadow-2xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                                class="px-3 py-1 rounded-full transition flex items-center gap-1.5">
                            <i data-lucide="map" class="w-3.5 h-3.5"></i>
                            <span>Spatial Digital Twin</span>
                        </button>
                    </div>

                    <div class="relative" x-show="campusView === 'grid'">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="text"
                               x-model="searchQuery"
                               placeholder="Search desk..."
                               class="pl-8 pr-3 py-1.5 rounded-full border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 bg-slate-50 dark:bg-[#142034] focus:outline-none focus:border-[#00c389] focus:bg-white dark:focus:bg-[#172338] transition w-32 sm:w-40">
                    </div>
                </div>
            </div>

            <!-- SPATIAL DIGITAL TWIN (INTERACTIVE 3D/ISOMETRIC CAMPUS MAP) -->
            <div x-show="campusView === 'spatial_map'" x-cloak x-transition class="space-y-4">
                <div class="p-4 rounded-2xl bg-slate-900 text-white relative overflow-hidden shadow-inner border border-slate-800">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#00c389] animate-ping"></span>
                            <span class="text-xs font-mono font-bold uppercase tracking-wider text-slate-300">BAUST Spatial Campus Twin</span>
                        </div>
                        <span class="text-[10px] font-mono text-[#00c389] bg-[#00c389]/10 px-2 py-0.5 rounded border border-[#00c389]/20">Live Radar Stream</span>
                    </div>

                    <!-- Isometric Map Grid Container -->
                    <div class="py-4 relative min-h-[340px] flex items-center justify-center">
                        <!-- Campus Background Schematic Graphic -->
                        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#00c389 1px, transparent 1px); background-size: 24px 24px;"></div>

                        <!-- Interactive Building Nodes Array -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 w-full z-10">
                            
                            <!-- Building 1: Academic Complex (REG & EXM) -->
                            <div @click="selectBuilding('academic')"
                                 :class="selectedBuilding === 'academic' ? 'border-[#00c389] ring-2 ring-[#00c389]/30 bg-slate-800' : 'border-slate-700 bg-slate-800/70 hover:border-slate-500'"
                                 class="p-4 rounded-2xl border cursor-pointer transition-all hover:scale-102 flex flex-col justify-between group">
                                <div class="flex items-center justify-between">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-mint-500/20 text-[#00c389] border border-mint-500/30">
                                        ACAD COMPLEX
                                    </span>
                                    <span class="relative flex h-2.5 w-2.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                                    </span>
                                </div>
                                <div class="my-3">
                                    <span class="text-sm font-bold font-display block text-white group-hover:text-[#00c389] transition">Academic Building 1</span>
                                    <span class="text-[11px] text-slate-400">Registrar (REG) & Exam Controller (EXM)</span>
                                </div>
                                <div class="flex items-center justify-between text-[10px] font-mono text-slate-400 pt-2 border-t border-slate-700/60">
                                    <span>8 Desks Staffed</span>
                                    <span class="text-[#00c389] font-bold">~4m wait</span>
                                </div>
                            </div>

                            <!-- Building 2: Administrative Wing (ACC & HAL) -->
                            <div @click="selectBuilding('admin_wing')"
                                 :class="selectedBuilding === 'admin_wing' ? 'border-[#00c389] ring-2 ring-[#00c389]/30 bg-slate-800' : 'border-slate-700 bg-slate-800/70 hover:border-slate-500'"
                                 class="p-4 rounded-2xl border cursor-pointer transition-all hover:scale-102 flex flex-col justify-between group">
                                <div class="flex items-center justify-between">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                        ADMIN WING
                                    </span>
                                    <span class="relative flex h-2.5 w-2.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                                    </span>
                                </div>
                                <div class="my-3">
                                    <span class="text-sm font-bold font-display block text-white group-hover:text-amber-400 transition">Administrative HQ</span>
                                    <span class="text-[11px] text-slate-400">Accounts & Finance (ACC), Hall (HAL)</span>
                                </div>
                                <div class="flex items-center justify-between text-[10px] font-mono text-slate-400 pt-2 border-t border-slate-700/60">
                                    <span>7 Desks Staffed</span>
                                    <span class="text-amber-400 font-bold">~6m wait</span>
                                </div>
                            </div>

                            <!-- Building 3: Engineering Block (CSE Dept) -->
                            <div @click="selectBuilding('engineering')"
                                 :class="selectedBuilding === 'engineering' ? 'border-[#00c389] ring-2 ring-[#00c389]/30 bg-slate-800' : 'border-slate-700 bg-slate-800/70 hover:border-slate-500'"
                                 class="p-4 rounded-2xl border cursor-pointer transition-all hover:scale-102 flex flex-col justify-between group">
                                <div class="flex items-center justify-between">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-cyan-500/20 text-cyan-400 border border-cyan-500/30">
                                        ENG BLOCK
                                    </span>
                                    <span class="relative flex h-2.5 w-2.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-cyan-500"></span>
                                    </span>
                                </div>
                                <div class="my-3">
                                    <span class="text-sm font-bold font-display block text-white group-hover:text-cyan-400 transition">Engineering Complex</span>
                                    <span class="text-[11px] text-slate-400">CSE Department Labs & Advising</span>
                                </div>
                                <div class="flex items-center justify-between text-[10px] font-mono text-slate-400 pt-2 border-t border-slate-700/60">
                                    <span>3 Desks Staffed</span>
                                    <span class="text-cyan-400 font-bold">~3m wait</span>
                                </div>
                            </div>

                            <!-- Building 4: Medical Center -->
                            <div @click="selectBuilding('medical')"
                                 :class="selectedBuilding === 'medical' ? 'border-[#00c389] ring-2 ring-[#00c389]/30 bg-slate-800' : 'border-slate-700 bg-slate-800/70 hover:border-slate-500'"
                                 class="p-4 rounded-2xl border cursor-pointer transition-all hover:scale-102 flex flex-col justify-between group">
                                <div class="flex items-center justify-between">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                        HEALTH CENTER
                                    </span>
                                    <span class="relative flex h-2.5 w-2.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                                    </span>
                                </div>
                                <div class="my-3">
                                    <span class="text-sm font-bold font-display block text-white group-hover:text-rose-400 transition">Campus Medical Center</span>
                                    <span class="text-[11px] text-slate-400">Health Check, Emergency Triage, Sick Leave</span>
                                </div>
                                <div class="flex items-center justify-between text-[10px] font-mono text-slate-400 pt-2 border-t border-slate-700/60">
                                    <span>4 Desks Staffed</span>
                                    <span class="text-rose-400 font-bold">~2m wait</span>
                                </div>
                            </div>

                            <!-- Building 5: Central Library & Digital Lounge -->
                            <div @click="selectBuilding('library')"
                                 :class="selectedBuilding === 'library' ? 'border-[#00c389] ring-2 ring-[#00c389]/30 bg-slate-800' : 'border-slate-700 bg-slate-800/70 hover:border-slate-500'"
                                 class="p-4 rounded-2xl border cursor-pointer transition-all hover:scale-102 flex flex-col justify-between group">
                                <div class="flex items-center justify-between">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                        WAITING LOUNGE
                                    </span>
                                    <i data-lucide="wifi" class="w-3.5 h-3.5 text-purple-400"></i>
                                </div>
                                <div class="my-3">
                                    <span class="text-sm font-bold font-display block text-white group-hover:text-purple-300 transition">Central Library Lounge</span>
                                    <span class="text-[11px] text-slate-400">Designated Quiet Standby Zone (AC & WiFi)</span>
                                </div>
                                <div class="flex items-center justify-between text-[10px] font-mono text-slate-400 pt-2 border-t border-slate-700/60">
                                    <span>Study & Wait</span>
                                    <span class="text-purple-300 font-bold">120 Seats</span>
                                </div>
                            </div>

                            <!-- Building 6: Campus Cafeteria -->
                            <div @click="selectBuilding('cafeteria')"
                                 :class="selectedBuilding === 'cafeteria' ? 'border-[#00c389] ring-2 ring-[#00c389]/30 bg-slate-800' : 'border-slate-700 bg-slate-800/70 hover:border-slate-500'"
                                 class="p-4 rounded-2xl border cursor-pointer transition-all hover:scale-102 flex flex-col justify-between group">
                                <div class="flex items-center justify-between">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        FOOD & DRINK
                                    </span>
                                    <i data-lucide="coffee" class="w-3.5 h-3.5 text-emerald-400"></i>
                                </div>
                                <div class="my-3">
                                    <span class="text-sm font-bold font-display block text-white group-hover:text-emerald-300 transition">Student Cafeteria</span>
                                    <span class="text-[11px] text-slate-400">Designated Casual Waiting Lounge</span>
                                </div>
                                <div class="flex items-center justify-between text-[10px] font-mono text-slate-400 pt-2 border-t border-slate-700/60">
                                    <span>Vibration Alert</span>
                                    <span class="text-emerald-300 font-bold">Live Paging</span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Selected Building Detail Drawer -->
                    <div x-show="selectedBuilding" x-transition class="mt-2 p-4 rounded-xl bg-slate-800/90 border border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                        <div>
                            <span class="text-[10px] font-mono font-bold uppercase text-[#00c389]" x-text="selectedBuildingData?.type"></span>
                            <h4 class="font-bold text-sm text-white" x-text="selectedBuildingData?.name"></h4>
                            <p class="text-slate-400 text-[11px] mt-0.5" x-text="selectedBuildingData?.directions"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded bg-slate-700 font-mono text-white text-[11px]" x-text="selectedBuildingData?.walk"></span>
                            <a :href="selectedBuildingData?.joinUrl || '{{ route('student.join') }}'"
                               class="px-4 py-2 rounded-full bg-[#00c389] hover:bg-emerald-600 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm transition">
                                <span>Get Digital Pass</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Campus Desks Cards Grid (Standard Grid Mode) -->
            <div x-show="campusView === 'grid'" x-transition class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($departments as $dept)
                    @php
                        $waitingCount = $dept->queueTickets()->where('status', 'WAITING')->count();
                        $activeCounters = $dept->counters()->whereIn('status', ['AVAILABLE', 'BUSY'])->count();
                        $totalCounters = $dept->counters()->count();
                    @endphp
                    <div x-show="!searchQuery || '{{ strtolower($dept->name . ' ' . $dept->code . ' ' . ($dept->location ?? '')) }}'.includes(searchQuery.toLowerCase())" x-transition class="bg-slate-50/70 dark:bg-[#121c2e]/70 hover:bg-white dark:hover:bg-[#162238] rounded-xl p-4 border border-slate-200/70 dark:border-slate-800 hover:border-[#00c389]/40 hover:shadow-card-hover transition-all duration-300 flex flex-col justify-between group">
                        <div class="space-y-3">
                            <!-- Visual Banner Badge -->
                            <div class="w-full h-24 rounded-lg bg-gradient-to-tr from-slate-800 to-slate-900 p-3 text-white flex flex-col justify-between relative overflow-hidden group-hover:scale-[1.02] transition-transform duration-300">
                                <div class="flex items-center justify-between z-10">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-white/20 backdrop-blur-md text-white border border-white/20">
                                        {{ $dept->code }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono {{ $waitingCount <= 2 ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white' }}">
                                        {{ $waitingCount }} in line
                                    </span>
                                </div>
                                <span class="font-display font-black text-sm tracking-tight z-10">{{ $dept->name }}</span>
                                <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-[#00c389]/20 rounded-full blur-xl pointer-events-none"></div>
                            </div>

                            <!-- Details -->
                            <div>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">{{ $dept->location ?? 'Campus Office' }}</span>
                                <span class="text-[11px] text-slate-400 dark:text-slate-500 font-mono block mt-0.5">
                                    {{ $activeCounters }}/{{ $totalCounters }} desks active • ~{{ $dept->avg_handling_time_min }}m avg
                                </span>
                            </div>
                        </div>

                        <!-- Action Footer -->
                        <div class="pt-3 mt-3 border-t border-slate-200/60 dark:border-slate-800 flex items-center justify-between">
                            <span class="text-[11px] font-bold text-[#00c389]">Available</span>
                            <a href="{{ route('student.join', ['dept_id' => $dept->id]) }}"
                               @click="window.playTactileSound('click')"
                               class="px-3 py-1.5 rounded-full bg-[#00c389] hover:bg-emerald-600 text-white font-bold text-xs transition flex items-center gap-1 shadow-xs shadow-mint group-hover:scale-105">
                                <span>Get Token</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Virtual Kiosk Thermal Printer Simulator (Interactive Experience) -->
            <div class="mt-4 p-5 rounded-xl bg-slate-50 dark:bg-[#121c2e] border border-slate-200/80 dark:border-slate-800 space-y-3 transition-colors duration-300">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#00c389] animate-pulse"></span>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider font-mono">Virtual Hallway Kiosk Dispenser</h4>
                    </div>
                    <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500">Terminal 01</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                    <div class="sm:col-span-8 flex flex-wrap gap-2">
                        <button type="button" @click="selectKioskDept('REG')"
                                :class="kioskDept === 'REG' ? 'bg-[#00c389] text-white border-transparent' : 'bg-white dark:bg-[#172338] text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-slate-300'"
                                class="px-3.5 py-1.5 rounded-full border text-xs font-bold transition">
                            Registrar (REG)
                        </button>
                        <button type="button" @click="selectKioskDept('ACC')"
                                :class="kioskDept === 'ACC' ? 'bg-[#00c389] text-white border-transparent' : 'bg-white dark:bg-[#172338] text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-slate-300'"
                                class="px-3.5 py-1.5 rounded-full border text-xs font-bold transition">
                            Accounts (ACC)
                        </button>
                        <button type="button" @click="selectKioskDept('EXM')"
                                :class="kioskDept === 'EXM' ? 'bg-[#00c389] text-white border-transparent' : 'bg-white dark:bg-[#172338] text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-slate-300'"
                                class="px-3.5 py-1.5 rounded-full border text-xs font-bold transition">
                            Exam Controller
                        </button>
                    </div>

                    <div class="sm:col-span-4 text-right">
                        <button type="button" @click="dispenseVirtualSlip()"
                                :disabled="dispensing"
                                class="w-full sm:w-auto px-4 py-2 rounded-full bg-slate-900 dark:bg-[#00c389] hover:bg-slate-800 dark:hover:bg-emerald-600 disabled:opacity-50 text-white font-bold text-xs shadow-sm transition flex items-center justify-center gap-1.5 ml-auto">
                            <i data-lucide="printer" class="w-3.5 h-3.5 text-[#00c389] dark:text-white"></i>
                            <span x-text="dispensing ? 'Dispensing...' : 'Print Sample Slip'"></span>
                        </button>
                    </div>
                </div>

                <!-- Dispensed Thermal Receipt Result -->
                <div x-show="dispensedSlip" x-transition class="mt-3 max-w-sm mx-auto bg-white dark:bg-[#162238] rounded-xl p-4 paper-receipt text-center space-y-2 border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center justify-between text-[10px] font-mono text-slate-400 border-b border-dashed border-slate-200 dark:border-slate-700 pb-2">
                        <span>CAMPUS DISPATCH</span>
                        <span>{{ now()->format('H:i') }}</span>
                    </div>
                    <span class="text-4xl font-black font-mono text-slate-900 dark:text-white block" x-text="dispensedSlip?.number"></span>
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 block" x-text="dispensedSlip?.service"></span>
                    <div class="p-2 rounded bg-slate-50 dark:bg-[#111927] text-[11px] font-mono text-slate-600 dark:text-slate-400 flex justify-around">
                        <span>Ahead: <strong x-text="dispensedSlip?.ahead"></strong></span>
                        <span>Wait: <strong x-text="dispensedSlip?.wait"></strong></span>
                    </div>
                    <a :href="'{{ route('student.join') }}'" class="block py-1.5 rounded-lg bg-[#00c389] text-white font-bold text-xs shadow-sm hover:bg-emerald-600 transition">
                        Claim Digital Pass on Phone &rarr;
                    </a>
                </div>
            </div>

        </div>

        <!-- Right 4 cols: Live Dispatch Activity & Chat/Summons Feed (Matching Reference "Chat") -->
        <div class="lg:col-span-4 bg-white dark:bg-[#0f172a] rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-card interactive-card flex flex-col justify-between transition-colors duration-300">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white font-display">Live Desk Activity</h3>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500">Summons stream across campus</p>
                    </div>
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-[#142034] border border-slate-100 dark:border-slate-700/80 px-2.5 py-1 rounded-lg">Today ▾</span>
                </div>

                <!-- Feed Items List (Matching Reference Avatar Chat Rows) -->
                <div class="divide-y divide-slate-100 dark:divide-slate-800 mt-2">
                    <div class="py-3 flex items-start gap-3 hover:bg-slate-50/70 dark:hover:bg-[#142034]/70 p-2 rounded-xl transition">
                        <div class="w-9 h-9 rounded-full bg-mint-100 dark:bg-mint-950/50 text-[#00c389] border border-mint-200 dark:border-mint-800/60 flex items-center justify-center font-bold text-xs shrink-0">
                            TH
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Tanvir Hasan</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">Just now</span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">Called to <strong class="text-slate-700 dark:text-slate-300">Counter 2</strong> (REG-104)</p>
                            <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[9px] font-bold font-mono bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                IN-CONSULTATION
                            </span>
                        </div>
                    </div>

                    <div class="py-3 flex items-start gap-3 hover:bg-slate-50/70 dark:hover:bg-[#142034]/70 p-2 rounded-xl transition">
                        <div class="w-9 h-9 rounded-full bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/60 flex items-center justify-center font-bold text-xs shrink-0">
                            SA
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Sadia Akter</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">4 min ago</span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">Fee payment completed at <strong class="text-slate-700 dark:text-slate-300">Counter 1</strong></p>
                            <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[9px] font-bold font-mono bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                COMPLETED
                            </span>
                        </div>
                    </div>

                    <div class="py-3 flex items-start gap-3 hover:bg-slate-50/70 dark:hover:bg-[#142034]/70 p-2 rounded-xl transition">
                        <div class="w-9 h-9 rounded-full bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-800/60 flex items-center justify-center font-bold text-xs shrink-0">
                            RH
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Rafiqul H.</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">10 min ago</span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">Joined <strong class="text-slate-700 dark:text-slate-300">Exam Controller</strong> queue</p>
                            <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[9px] font-bold font-mono bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50">
                                POSITION #2
                            </span>
                        </div>
                    </div>

                    <div class="py-3 flex items-start gap-3 hover:bg-slate-50/70 dark:hover:bg-[#142034]/70 p-2 rounded-xl transition">
                        <div class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 flex items-center justify-center font-bold text-xs shrink-0">
                            MA
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Mahmudul A.</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">15 min ago</span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">Clearance attested at <strong class="text-slate-700 dark:text-slate-300">Medical Center</strong></p>
                            <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[9px] font-bold font-mono bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                COMPLETED
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TV Signage Quick Jump Button -->
            <div class="pt-4 mt-2 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('public.display') }}" target="_blank"
                   class="w-full py-2.5 rounded-full bg-slate-50 dark:bg-[#142034] hover:bg-slate-100 dark:hover:bg-[#1b2b46] border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-2xs">
                    <i data-lucide="tv" class="w-3.5 h-3.5 text-rose-500"></i>
                    <span>Open 4K Public Lobby Display &rarr;</span>
                </a>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
function homeDashboard() {
    return {
        metrics: {
            waiting_students: 24,
            active_counters: 14,
            completed_today: 482,
            avg_wait_time_min: 4.5
        },
        searchQuery: '',
        campusView: 'grid',
        selectedBuilding: 'academic',
        kioskDept: 'REG',
        dispensing: false,
        dispensedSlip: null,

        buildingsInfo: {
            academic: {
                type: 'ACADEMIC COMPLEX',
                name: 'Academic Building 1 (Registrar & Exam Controller)',
                directions: 'Ground Floor, Wing A (Rooms 101-108). Accessible from Main Entrance Gate 1.',
                walk: '3 min walk from Library',
                joinUrl: '{{ route('student.join', ['dept_id' => 1]) }}'
            },
            admin_wing: {
                type: 'ADMINISTRATIVE HQ',
                name: 'Administrative Building (Accounts & Finance, Hall Office)',
                directions: 'Ground Floor & 1st Floor. Tuition payment counter accepts challans & mobile banking.',
                walk: '2 min walk from Main Plaza',
                joinUrl: '{{ route('student.join', ['dept_id' => 2]) }}'
            },
            engineering: {
                type: 'ENGINEERING COMPLEX',
                name: 'Engineering Building (CSE Department Office)',
                directions: 'Level 3, North Wing. Hardware lab clearance & academic advising sign-offs.',
                walk: '4 min walk from Cafeteria',
                joinUrl: '{{ route('student.join', ['dept_id' => 5]) }}'
            },
            medical: {
                type: 'HEALTH CENTER',
                name: 'University Medical Center',
                directions: 'East Wing Pavilion. Dedicated emergency triage desk & medical sick leave attestation.',
                walk: '1 min walk from Central Field',
                joinUrl: '{{ route('student.join', ['dept_id' => 6]) }}'
            },
            library: {
                type: 'DESIGNATED WAITING LOUNGE',
                name: 'Central University Library (Levels 1 & 2)',
                directions: 'High-speed campus WiFi, quiet study cubicles, and digital queue monitors on walls.',
                walk: 'Campus Central',
                joinUrl: '{{ route('student.join') }}'
            },
            cafeteria: {
                type: 'CASUAL WAITING LOUNGE',
                name: 'Student Cafeteria & Recreation Hub',
                directions: 'Air-conditioned seating. Your phone will chime when you are 2 positions away from desk call.',
                walk: 'Central Plaza',
                joinUrl: '{{ route('student.join') }}'
            }
        },

        get selectedBuildingData() {
            return this.buildingsInfo[this.selectedBuilding] || this.buildingsInfo.academic;
        },

        selectBuilding(key) {
            this.selectedBuilding = key;
            if (window.playTactileSound) window.playTactileSound('click');
        },

        init() {
            lucide.createIcons();
            this.fetchLiveMetrics();
            setInterval(() => this.fetchLiveMetrics(), 5000);
        },

        async fetchLiveMetrics() {
            try {
                const res = await fetch('/api/display/live');
                if (res.ok) {
                    const json = await res.json();
                    if (json.success && json.data && json.data.summary) {
                        this.metrics = json.data.summary;
                    }
                }
            } catch(e) {}
        },

        selectKioskDept(code) {
            if (window.playTactileSound) window.playTactileSound('click');
            this.kioskDept = code;
        },

        dispenseVirtualSlip() {
            this.dispensing = true;
            if (window.playTactileSound) window.playTactileSound('dispense');

            setTimeout(() => {
                const num = Math.floor(100 + Math.random() * 90);
                this.dispensedSlip = {
                    number: this.kioskDept + '-' + num,
                    service: this.kioskDept === 'REG' ? 'Transcript Attestation' : (this.kioskDept === 'ACC' ? 'Tuition Fee Clearance' : 'Exam Admit Card'),
                    ahead: Math.floor(1 + Math.random() * 3) + ' students',
                    wait: '~5 min'
                };
                this.dispensing = false;
                if (window.playTactileSound) window.playTactileSound('chime');
                this.$nextTick(() => lucide.createIcons());
            }, 600);
        }
    }
}
</script>
@endpush
@endsection
