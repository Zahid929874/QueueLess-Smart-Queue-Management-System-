@extends('layouts.app', ['title' => 'My Queue Ticket Tracker'])

@section('content')
<div x-data="ticketTracker()" x-init="init()" class="max-w-2xl mx-auto space-y-6">

    <!-- Active Ticket View -->
    <template x-if="ticket">
        <div class="space-y-5">

            <!-- High-Priority Banner Alert When Called -->
            <div x-show="ticket.status === 'CALLED'" x-transition
                 class="p-6 rounded-2xl bg-gradient-to-r from-[#00c389] to-emerald-600 text-white shadow-mint animate-pulse flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center shrink-0">
                        <i data-lucide="bell-ring" class="w-6 h-6 text-white animate-bounce"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest bg-white/20 px-2 py-0.5 rounded inline-block mb-1 font-mono">
                            DESK SUMMONS ACTIVE
                        </span>
                        <h2 class="text-xl font-black tracking-tight text-white font-display">IT'S YOUR TURN!</h2>
                        <p class="text-xs text-white/90 font-medium mt-0.5">
                            Please proceed immediately to <strong class="underline font-black text-sm font-mono text-white" x-text="ticket.counter ? ticket.counter.name : 'designated counter'"></strong>.
                        </p>
                    </div>
                </div>
                <button @click="playChime()" class="px-5 py-2.5 rounded-full bg-white text-slate-900 hover:bg-slate-50 text-xs font-black shrink-0 transition flex items-center justify-center gap-1.5 shadow-md">
                    <i data-lucide="volume-2" class="w-4 h-4 text-[#00c389]"></i> Repeat Chime
                </button>
            </div>

            <!-- Serving Banner -->
            <div x-show="ticket.status === 'SERVING'" x-transition class="p-4 rounded-2xl bg-mint-50 border border-mint-200 text-mint-900 shadow-xs flex items-center gap-3">
                <i data-lucide="check-check" class="w-5 h-5 text-[#00c389] shrink-0"></i>
                <div class="text-xs">
                    <strong class="font-bold text-slate-900">In-Consultation:</strong> You are actively being served at <span class="font-bold underline text-[#00c389] font-mono" x-text="ticket.counter ? ticket.counter.name : 'Counter Desk'"></span>.
                </div>
            </div>

            <!-- Approaching Turn Alert (When 1-2 students ahead) -->
            <div x-show="ticket.status === 'WAITING' && peopleAhead <= 2" x-transition
                 class="p-4 rounded-2xl bg-gradient-to-r from-amber-50 to-emerald-50 border border-amber-300/80 text-amber-950 shadow-xs flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full bg-amber-500 animate-ping"></span>
                    <div class="text-xs">
                        <strong class="font-black text-amber-900 uppercase tracking-wide">Almost Your Turn!</strong>
                        <span class="text-slate-700 ml-1">Only <strong class="font-mono text-slate-900" x-text="peopleAhead"></strong> student(s) ahead. Please move toward <span class="font-bold text-emerald-800" x-text="ticket.department.name"></span>.</span>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-mono font-bold bg-white border border-amber-200 text-amber-800 shrink-0">~<span x-text="estimatedWait || 2"></span>m left</span>
            </div>

            <!-- Visual 4-Step Queue Progress Bar -->
            <div class="no-print bg-white dark:bg-[#0f172a] border border-slate-100 dark:border-slate-800 rounded-2xl p-6 shadow-card interactive-card transition-colors duration-300">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 font-mono">Queue Pipeline Clearance</span>
                    <span class="text-xs font-mono text-[#00c389] font-bold" x-text="'Position #' + (position || 1)"></span>
                </div>
                <div class="grid grid-cols-4 gap-2 text-center text-xs">
                    <div class="space-y-1.5">
                        <div class="h-2 rounded-full transition-all duration-500" :class="ticket ? 'bg-[#00c389]' : 'bg-slate-100 dark:bg-slate-800'"></div>
                        <span class="font-bold text-[11px] font-mono block" :class="ticket ? 'text-[#00c389]' : 'text-slate-400 dark:text-slate-500'">1. In Queue</span>
                    </div>
                    <div class="space-y-1.5">
                        <div class="h-2 rounded-full transition-all duration-500" :class="peopleAhead <= 3 || ['CALLED', 'SERVING'].includes(ticket.status) ? 'bg-[#00c389]' : 'bg-slate-100 dark:bg-slate-800'"></div>
                        <span class="font-bold text-[11px] font-mono block" :class="peopleAhead <= 3 || ['CALLED', 'SERVING'].includes(ticket.status) ? 'text-[#00c389]' : 'text-slate-400 dark:text-slate-500'">2. Near Front</span>
                    </div>
                    <div class="space-y-1.5">
                        <div class="h-2 rounded-full transition-all duration-500" :class="['CALLED', 'SERVING'].includes(ticket.status) ? 'bg-[#00c389]' : 'bg-slate-100 dark:bg-slate-800'"></div>
                        <span class="font-bold text-[11px] font-mono block" :class="['CALLED', 'SERVING'].includes(ticket.status) ? 'text-[#00c389]' : 'text-slate-400 dark:text-slate-500'">3. Summoned</span>
                    </div>
                    <div class="space-y-1.5">
                        <div class="h-2 rounded-full transition-all duration-500" :class="ticket.status === 'SERVING' ? 'bg-emerald-600' : 'bg-slate-100 dark:bg-slate-800'"></div>
                        <span class="font-bold text-[11px] font-mono block" :class="ticket.status === 'SERVING' ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500'">4. Serving</span>
                    </div>
                </div>
            </div>

            <!-- Pass Style Switcher Pill -->
            <div class="flex items-center justify-between no-print px-1">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#00c389]"></i>
                    <span>Digital Boarding Pass Style</span>
                </span>
                <div class="flex items-center p-0.5 rounded-full bg-slate-100 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-xs font-bold">
                    <button type="button" @click="passStyle = 'standard'; if (window.playTactileSound) window.playTactileSound('click')"
                            :class="passStyle === 'standard' ? 'bg-white dark:bg-[#1e293b] text-slate-900 dark:text-white shadow-2xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                            class="px-3 py-1 rounded-full transition flex items-center gap-1">
                        <span>Clean Pass</span>
                    </button>
                    <button type="button" @click="passStyle = 'wallet'; if (window.playTactileSound) window.playTactileSound('click')"
                            :class="passStyle === 'wallet' ? 'bg-slate-900 dark:bg-[#00c389] text-mint-accent dark:text-slate-950 shadow-2xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                            class="px-3 py-1 rounded-full transition flex items-center gap-1.5">
                        <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                        <span>Holographic Pass</span>
                    </button>
                </div>
            </div>

            <!-- Primary Boarding-Pass Style Ticket Card with Physical Cutout Notches -->
            <div id="ticketSlip"
                 :class="passStyle === 'wallet' ? 'bg-slate-950 text-white holographic-sheen border-slate-800 shadow-2xl' : 'bg-white dark:bg-[#0f172a] text-slate-900 dark:text-white border-slate-100 dark:border-slate-800 shadow-card'"
                 class="rounded-3xl p-7 sm:p-8 border relative overflow-hidden interactive-card transition-all duration-300">
                
                <!-- Status & Department Badge -->
                <div class="flex items-center justify-between pb-5 border-b"
                     :class="passStyle === 'wallet' ? 'border-slate-800' : 'border-slate-100 dark:border-slate-800'">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-black font-mono"
                              :class="passStyle === 'wallet' ? 'bg-[#00c389] text-slate-950 shadow-xs' : 'bg-slate-900 dark:bg-[#00c389] text-white dark:text-slate-950'"
                              x-text="ticket.department.code"></span>
                        <span class="text-sm font-extrabold font-display"
                              :class="passStyle === 'wallet' ? 'text-white' : 'text-slate-900 dark:text-white'"
                              x-text="ticket.department.name"></span>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-black tracking-wide uppercase font-mono"
                          :class="{
                              'bg-amber-100 text-amber-800 border border-amber-200': ticket.status === 'WAITING' && passStyle !== 'wallet',
                              'bg-amber-500/20 text-amber-300 border border-amber-500/30': ticket.status === 'WAITING' && passStyle === 'wallet',
                              'bg-mint-100 text-[#00c389] border border-mint-200 animate-pulse': ticket.status === 'CALLED',
                              'bg-emerald-100 text-emerald-800 border border-emerald-200': ticket.status === 'SERVING'
                          }"
                          x-text="ticket.status">
                    </span>
                </div>

                <!-- Big Bold Token Number -->
                <div class="text-center py-7">
                    <span class="text-[10px] uppercase font-mono font-bold tracking-widest block mb-1"
                          :class="passStyle === 'wallet' ? 'text-mint-accent' : 'text-slate-400 dark:text-slate-500'">
                        DIGITAL QUEUE PASS
                    </span>
                    <h1 class="text-6xl sm:text-7xl font-black tracking-tight font-mono"
                        :class="passStyle === 'wallet' ? 'text-white drop-shadow-md' : 'text-slate-900 dark:text-white'"
                        x-text="ticket.ticket_number"></h1>
                    <div class="inline-flex items-center gap-1.5 mt-3 px-3.5 py-1.5 rounded-full text-xs font-bold"
                         :class="passStyle === 'wallet' ? 'bg-white/10 text-white border border-white/20' : 'bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200'">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5 text-[#00c389]"></i>
                        <span x-text="ticket.service.name"></span>
                    </div>
                </div>

                <!-- 4 KPI Metrics Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 rounded-2xl text-center font-mono"
                     :class="passStyle === 'wallet' ? 'bg-slate-900/90 border border-slate-800' : 'bg-slate-50 dark:bg-[#142034] border border-slate-100 dark:border-slate-800'">
                    <div>
                        <span class="text-[10px] uppercase font-bold block" :class="passStyle === 'wallet' ? 'text-slate-400' : 'text-slate-400 dark:text-slate-500'">Queue Rank</span>
                        <span class="text-2xl font-black" :class="passStyle === 'wallet' ? 'text-white' : 'text-slate-900 dark:text-white'" x-text="'#' + (position || 1)"></span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold block" :class="passStyle === 'wallet' ? 'text-slate-400' : 'text-slate-400 dark:text-slate-500'">Ahead Of You</span>
                        <span class="text-2xl font-black" :class="passStyle === 'wallet' ? 'text-white' : 'text-slate-900 dark:text-white'" x-text="peopleAhead"></span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold block" :class="passStyle === 'wallet' ? 'text-slate-400' : 'text-slate-400 dark:text-slate-500'">Est. Wait</span>
                        <span class="text-2xl font-black text-[#00c389]" x-text="countdownText || ((estimatedWait || ticket.estimated_wait_time_min) + 'm')"></span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold block" :class="passStyle === 'wallet' ? 'text-slate-400' : 'text-slate-400 dark:text-slate-500'">Desk Serving</span>
                        <span class="text-2xl font-black" :class="passStyle === 'wallet' ? 'text-slate-300' : 'text-slate-800 dark:text-slate-200'" x-text="nowServingToken || '—'"></span>
                    </div>
                </div>

                <!-- Expected Clock Time & Details -->
                <div class="mt-6 flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-2 border-t pt-4"
                     :class="passStyle === 'wallet' ? 'border-slate-800 text-slate-400' : 'border-slate-100 dark:border-slate-800 text-slate-500 dark:text-slate-400'">
                    <div class="flex items-center gap-2">
                        <i data-lucide="clock" class="w-4 h-4 text-[#00c389]"></i>
                        <span>Expected Turn: <strong class="font-mono text-sm" :class="passStyle === 'wallet' ? 'text-white' : 'text-slate-900 dark:text-white'" x-text="expectedCallTime"></strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4 text-slate-400 dark:text-slate-500"></i>
                        <span>Location: <strong :class="passStyle === 'wallet' ? 'text-white' : 'text-slate-700 dark:text-slate-300'" x-text="ticket.department.location || 'Campus Desk'"></strong></span>
                    </div>
                </div>

                <!-- Simulated Linear Optical Barcode -->
                <div class="mt-6 pt-4 border-t text-center"
                     :class="passStyle === 'wallet' ? 'border-slate-800' : 'border-slate-100 dark:border-slate-800'">
                    <div class="h-10 max-w-xs mx-auto flex items-center justify-center gap-1 opacity-80">
                        <template x-for="i in 36" :key="i">
                            <span class="h-full inline-block rounded-xs"
                                  :class="passStyle === 'wallet' ? 'bg-slate-200' : 'bg-slate-900 dark:bg-slate-200'"
                                  :style="'width: ' + ((i % 3 === 0) ? '3px' : (i % 2 === 0 ? '1.5px' : '1px'))"></span>
                        </template>
                    </div>
                    <span class="text-[9px] font-mono tracking-widest mt-1 block"
                          :class="passStyle === 'wallet' ? 'text-slate-400' : 'text-slate-400 dark:text-slate-500'"
                          x-text="'*' + (ticket.ticket_number || 'TK-000') + '*'"></span>
                </div>

                <!-- Slip Tools: QR Code, Print, NFC Scanner & Notifications -->
                <div class="no-print mt-6 pt-4 border-t flex flex-wrap items-center justify-between gap-3"
                     :class="passStyle === 'wallet' ? 'border-slate-800' : 'border-slate-100 dark:border-slate-800'">
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Simulate NFC Tap Button -->
                        <button @click="simulateNfcTap()"
                                class="px-3.5 py-2 rounded-full bg-[#00c389] hover:bg-emerald-600 text-white text-xs font-bold flex items-center gap-1.5 shadow-sm shadow-mint hover:scale-105 transition">
                            <i data-lucide="nfc" class="w-3.5 h-3.5"></i>
                            <span>Tap Kiosk NFC</span>
                        </button>
                        <button @click="showQr = !showQr"
                                class="px-3.5 py-2 rounded-full border text-xs font-bold flex items-center gap-1.5 shadow-2xs hover:scale-105 transition"
                                :class="passStyle === 'wallet' ? 'border-slate-700 text-white hover:bg-slate-800' : 'border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#142034]'">
                            <i data-lucide="qr-code" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span x-text="showQr ? 'Hide QR' : 'Show Optical QR'"></span>
                        </button>
                        <button @click="printTicket()"
                                class="px-3 py-2 rounded-full border text-xs font-bold flex items-center gap-1.5 shadow-2xs hover:scale-105 transition"
                                :class="passStyle === 'wallet' ? 'border-slate-700 text-white hover:bg-slate-800' : 'border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#142034]'">
                            <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-400"></i> Print Slip
                        </button>
                        <button @click="playChime()"
                                class="px-3 py-2 rounded-full border text-xs font-bold flex items-center gap-1.5 shadow-2xs hover:scale-105 transition"
                                :class="passStyle === 'wallet' ? 'border-slate-700 text-white hover:bg-slate-800' : 'border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#142034]'"
                                title="Test notification sound">
                            <i data-lucide="volume-2" class="w-3.5 h-3.5 text-[#00c389]"></i> Chime
                        </button>
                    </div>

                    <button x-show="ticket.status === 'WAITING'" @click="cancelTicket(ticket.id)" class="px-4 py-2 rounded-full border border-rose-400 text-rose-500 hover:bg-rose-50 text-xs font-bold transition">
                        Leave Queue
                    </button>
                </div>

                <!-- QR Code Box -->
                <div x-show="showQr" x-cloak class="mt-4 p-5 rounded-2xl text-center space-y-3"
                     :class="passStyle === 'wallet' ? 'bg-slate-900 border border-slate-800 text-white' : 'bg-slate-50 border border-slate-200 text-slate-900'">
                    <div class="w-36 h-36 bg-white p-2.5 rounded-2xl mx-auto border border-slate-200 flex flex-col items-center justify-center font-mono text-[10px] font-bold text-slate-800 shadow-xs">
                        <i data-lucide="qr-code" class="w-20 h-20 text-slate-900"></i>
                        <span class="block text-[10px] font-mono font-bold text-slate-700 mt-1" x-text="ticket.ticket_number"></span>
                    </div>
                    <div>
                        <span class="text-xs font-extrabold block">Counter Optical Scanner Pass</span>
                        <span class="text-[11px] text-slate-400 block mt-0.5">Hold this pass near the desk optical scanner when your ticket is summoned</span>
                    </div>
                </div>
            </div>

            <!-- INTERACTIVE DOCUMENT READINESS CHECKLIST (Standout Student Capability) -->
            <div class="no-print bg-white dark:bg-[#0f172a] rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-card interactive-card space-y-4 transition-colors duration-300">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-mint-50 dark:bg-mint-950/50 text-[#00c389] flex items-center justify-center font-bold">
                            <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white font-display">Required Documents Checklist</h3>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">Verify your documents before walking up to the service desk</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-mono font-bold"
                          :class="docsCheckedCount === 4 ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60'"
                          x-text="docsCheckedCount + '/4 Ready'">
                    </span>
                </div>

                <div class="space-y-2 text-xs">
                    <label class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-[#142034] border border-slate-100 dark:border-slate-800 cursor-pointer transition">
                        <input type="checkbox" x-model="doc1" class="w-4 h-4 rounded text-[#00c389] focus:ring-[#00c389] border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800">
                        <div class="flex-1">
                            <span class="font-bold text-slate-800 dark:text-slate-200 block">Original Student ID Card</span>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500">Must display active semester sticker and clear photo</span>
                        </div>
                        <i data-lucide="check" class="w-4 h-4 text-[#00c389]" x-show="doc1"></i>
                    </label>

                    <label class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-[#142034] border border-slate-100 dark:border-slate-800 cursor-pointer transition">
                        <input type="checkbox" x-model="doc2" class="w-4 h-4 rounded text-[#00c389] focus:ring-[#00c389] border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800">
                        <div class="flex-1">
                            <span class="font-bold text-slate-800 dark:text-slate-200 block">Tuition / Service Fee Deposit Slip</span>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500">Bank challan or verified mobile money Transaction ID receipt</span>
                        </div>
                        <i data-lucide="check" class="w-4 h-4 text-[#00c389]" x-show="doc2"></i>
                    </label>

                    <label class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-[#142034] border border-slate-100 dark:border-slate-800 cursor-pointer transition">
                        <input type="checkbox" x-model="doc3" class="w-4 h-4 rounded text-[#00c389] focus:ring-[#00c389] border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800">
                        <div class="flex-1">
                            <span class="font-bold text-slate-800 dark:text-slate-200 block">Academic Clearance / Form Copies</span>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500">2 photocopies of documents requiring attestation or verification</span>
                        </div>
                        <i data-lucide="check" class="w-4 h-4 text-[#00c389]" x-show="doc3"></i>
                    </label>

                    <label class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-[#142034] border border-slate-100 dark:border-slate-800 cursor-pointer transition">
                        <input type="checkbox" x-model="doc4" class="w-4 h-4 rounded text-[#00c389] focus:ring-[#00c389] border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800">
                        <div class="flex-1">
                            <span class="font-bold text-slate-800 dark:text-slate-200 block">Department Advisor / Hall Clearance (If Required)</span>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500">Initial sign-off for semester registration and admit release</span>
                        </div>
                        <i data-lucide="check" class="w-4 h-4 text-[#00c389]" x-show="doc4"></i>
                    </label>
                </div>
            </div>

            <!-- SIMULATED MOBILE LOCKSCREEN PUSH ALERT (Standout Real-World Simulation) -->
            <div class="no-print bg-slate-900 text-white rounded-2xl p-5 border border-slate-800 shadow-xl space-y-3">
                <div class="flex items-center justify-between text-[10px] font-mono text-slate-400">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="smartphone" class="w-3.5 h-3.5 text-mint-accent"></i>
                        <span>Mobile Push / SMS Notification Preview</span>
                    </span>
                    <span>BAUST PUSH GATEWAY</span>
                </div>

                <div class="p-3.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/10 text-xs space-y-1">
                    <div class="flex items-center justify-between text-[10px]">
                        <span class="font-bold text-mint-accent flex items-center gap-1">
                            <i data-lucide="bell" class="w-3 h-3"></i> QueueLess Alert
                        </span>
                        <span class="text-white/60 font-mono">Now</span>
                    </div>
                    <p class="text-white text-xs font-semibold leading-relaxed">
                        Ticket <span class="font-mono text-mint-accent" x-text="ticket.ticket_number"></span>: You are <span x-text="peopleAhead"></span> student(s) away from the desk! Please head toward <span x-text="ticket.department.name"></span>.
                    </p>
                </div>
            </div>

            <!-- SIMULATED NFC SCANNER MODAL -->
            <div x-show="nfcModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-md p-4">
                <div @click.away="nfcModalOpen = false" class="bg-white dark:bg-[#0c1322] rounded-3xl max-w-sm w-full p-6 text-center space-y-4 shadow-2xl border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white">
                    <div class="w-20 h-20 rounded-full bg-mint-50 dark:bg-mint-950/50 border-2 border-[#00c389] mx-auto flex items-center justify-center text-[#00c389] animate-pulse relative">
                        <i data-lucide="nfc" class="w-10 h-10"></i>
                        <span class="absolute inset-0 rounded-full border-2 border-[#00c389] animate-ping opacity-40"></span>
                    </div>
                    <div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 uppercase">
                            NFC Check-In Confirmed
                        </span>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mt-2 font-display">Fast-Pass Verified</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Gate turnstile unlocked. Counter operator has received your student profile.
                        </p>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-100 dark:border-slate-700 text-xs font-mono text-slate-700 dark:text-slate-200">
                        <div>Ticket: <strong x-text="ticket.ticket_number"></strong></div>
                        <div>Dept: <strong x-text="ticket.department.code"></strong></div>
                        <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Timestamp: {{ now()->format('H:i:s') }} • Latency: 12ms</div>
                    </div>
                    <button @click="nfcModalOpen = false" class="w-full py-2.5 rounded-full bg-slate-900 dark:bg-[#00c389] hover:bg-slate-800 dark:hover:bg-emerald-600 text-white font-bold text-xs transition">
                        Done
                    </button>
                </div>
            </div>

            <!-- Campus Standby Guidance -->
            <div class="no-print p-5 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-100 dark:border-slate-800 shadow-card interactive-card text-xs text-slate-600 dark:text-slate-300 space-y-2 transition-colors duration-300">
                <span class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="coffee" class="w-4 h-4 text-[#00c389]"></i>
                    Campus Standby Guidance:
                </span>
                <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                    You do not need to stand in the hallway. You are welcome to wait in the <strong>Central Library</strong> or <strong>Cafeteria</strong>. Your phone will chime when you are 2 students away from being called.
                </p>
            </div>
        </div>
    </template>

    <!-- Empty State (No Active Ticket) -->
    <template x-if="!ticket && !loading">
        <div class="bg-white dark:bg-[#0f172a] border border-slate-100 dark:border-slate-800 rounded-2xl p-8 sm:p-12 text-center space-y-6 shadow-card interactive-card text-slate-900 dark:text-white transition-colors duration-300">
            <div class="w-16 h-16 rounded-2xl bg-mint-50 dark:bg-mint-950/50 border border-mint-100 dark:border-mint-800/60 text-[#00c389] flex items-center justify-center mx-auto shadow-sm">
                <i data-lucide="ticket" class="w-8 h-8"></i>
            </div>
            <div class="space-y-1.5">
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight font-display">No Active Queue Token</h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                    You do not currently have a waiting slip. Choose a campus service desk below to take a digital token.
                </p>
            </div>

            <div class="pt-2">
                <a href="{{ route('student.join') }}" @click="window.playTactileSound('click')"
                   class="inline-flex items-center gap-2 px-7 py-3.5 rounded-full bg-[#00c389] hover:bg-emerald-600 text-white font-bold text-sm shadow-md shadow-mint active:scale-95 transition-all">
                    <i data-lucide="printer" class="w-4 h-4"></i> Take A Digital Token Now
                </a>
            </div>

            <!-- Quick Department List -->
            <div class="border-t border-slate-100 dark:border-slate-800 pt-6 text-left">
                <span class="text-xs font-mono font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-3">Campus Service Offices:</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($departments as $d)
                        <a href="{{ route('student.join', ['dept_id' => $d->id]) }}" @click="window.playTactileSound('click')"
                           class="p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-[#121c2e]/60 hover:border-[#00c389] hover:bg-white dark:hover:bg-[#162238] transition-all flex items-center justify-between text-xs group">
                            <div>
                                <span class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-[#00c389] transition">{{ $d->name }}</span>
                                <span class="text-slate-400 dark:text-slate-500 block text-[11px] font-mono mt-0.5">{{ $d->code }} • ~{{ $d->avg_handling_time_min }}m avg handling</span>
                            </div>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-[#00c389] group-hover:translate-x-1 transition"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </template>

</div>

@push('scripts')
<script>
function ticketTracker() {
    return {
        ticket: @json($activeTicket),
        peopleAhead: {{ $peopleAhead }},
        position: {{ $position }},
        estimatedWait: {{ $activeTicket->estimated_wait_time_min ?? 0 }},
        nowServingToken: '{{ $currentServing?->ticket_number ?? 'None' }}',
        activeCounters: {{ $activeCounters }},
        expectedCallTime: '',
        countdownSeconds: 0,
        countdownText: '',
        showQr: false,
        passStyle: 'wallet',
        nfcModalOpen: false,
        doc1: true,
        doc2: true,
        doc3: true,
        doc4: false,
        loading: false,
        pollTimer: null,
        countdownInterval: null,

        get docsCheckedCount() {
            return (this.doc1 ? 1 : 0) + (this.doc2 ? 1 : 0) + (this.doc3 ? 1 : 0) + (this.doc4 ? 1 : 0);
        },

        simulateNfcTap() {
            if (window.playTactileSound) window.playTactileSound('nfc');
            this.nfcModalOpen = true;
            if (window.showToast) window.showToast('Kiosk NFC Reader: Student Roll 1904001 confirmed!', 'success');
            this.$nextTick(() => lucide.createIcons());
        },

        init() {
            lucide.createIcons();
            this.countdownSeconds = Math.max(60, (this.estimatedWait || 5) * 60);
            this.updateCountdownDisplay();
            this.calculateExpectedTime();

            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }

            this.countdownInterval = setInterval(() => {
                if (this.countdownSeconds > 0) {
                    this.countdownSeconds--;
                    this.updateCountdownDisplay();
                }
            }, 1000);

            this.pollTimer = setInterval(() => {
                this.syncActiveTicket();
            }, 2000);
        },

        updateCountdownDisplay() {
            const m = Math.floor(this.countdownSeconds / 60);
            const s = this.countdownSeconds % 60;
            this.countdownText = `${m}m ${s < 10 ? '0' : ''}${s}s`;
        },

        calculateExpectedTime() {
            const waitMin = this.estimatedWait || (this.ticket ? this.ticket.estimated_wait_time_min : 0) || 5;
            this.countdownSeconds = Math.max(30, waitMin * 60);
            const target = new Date(Date.now() + waitMin * 60000);
            this.expectedCallTime = target.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },

        async copyShareLink() {
            try {
                await navigator.clipboard.writeText(window.location.href);
                if (window.showToast) window.showToast('Live tracking pass link copied to clipboard!', 'success');
            } catch (err) {
                if (window.showToast) window.showToast('Link: ' + window.location.href, 'info');
            }
        },

        async syncActiveTicket() {
            try {
                const res = await fetch('/api/queues/active', {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const json = await res.json();
                if (json.success) {
                    const prevStatus = this.ticket ? this.ticket.status : null;
                    this.ticket = json.data.active_ticket;
                    this.position = json.data.position;
                    this.peopleAhead = json.data.people_ahead;
                    this.estimatedWait = json.data.estimated_wait_min;
                    this.nowServingToken = json.data.current_serving_token;
                    this.activeCounters = json.data.active_counters;
                    this.calculateExpectedTime();

                    if (this.ticket && this.ticket.status === 'CALLED' && prevStatus === 'WAITING') {
                        this.playChime();
                        if ('vibrate' in navigator) {
                            navigator.vibrate([200, 100, 200, 100, 400]);
                        }
                        if ('Notification' in window && Notification.permission === 'granted') {
                            new Notification('Token Called to Desk!', {
                                body: `Your token ${this.ticket.ticket_number} has been called to ${this.ticket.counter ? this.ticket.counter.name : 'Counter'}!`
                            });
                        }
                    }

                    this.$nextTick(() => lucide.createIcons());
                }
            } catch (e) {
                console.error("Sync error:", e);
            }
        },

        playChime() {
            if (window.playTactileSound) {
                window.playTactileSound('chime');
            }
        },

        printTicket() {
            window.print();
        },

        async cancelTicket(id) {
            if (!confirm("Are you sure you want to cancel your queue token?")) return;
            try {
                const res = await fetch(`/api/queues/${id}/cancel`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ reason: 'Cancelled by student' })
                });
                const json = await res.json();
                if (json.success) {
                    if (window.showToast) window.showToast("Queue token cancelled.", "info");
                    this.ticket = null;
                } else {
                    alert(json.message || "Failed to cancel ticket.");
                }
            } catch (err) {
                alert("Error cancelling ticket.");
            }
        }
    }
}
</script>
@endpush
@endsection
