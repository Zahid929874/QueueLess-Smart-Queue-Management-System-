<!DOCTYPE html>
<html lang="en" class="h-full bg-[#040e1d] text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QueueLess — Smart Digital Signage & TV Display</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700;800;900&family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'Plus Jakarta Sans', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        [x-cloak] { display: none !important; }
        @keyframes pulse-ring {
            0% { transform: scale(0.97); opacity: 0.85; }
            50% { transform: scale(1.01); opacity: 1; }
            100% { transform: scale(0.97); opacity: 0.85; }
        }
        .animate-token-glow {
            animation: pulse-ring 2.5s infinite ease-in-out;
        }
    </style>
</head>
<body class="h-full flex flex-col font-sans select-none overflow-hidden bg-[#040e1d] text-slate-100 relative" x-data="tvSignage()" x-init="init()">

    <!-- Ambient glowing radial backlights -->
    <div class="absolute -top-32 left-1/4 w-[650px] h-[650px] bg-teal-500/10 rounded-full blur-[140px] pointer-events-none"></div>
    <div class="absolute bottom-10 -right-32 w-[600px] h-[600px] bg-indigo-500/10 rounded-full blur-[140px] pointer-events-none"></div>

    <!-- Header Bar -->
    <header class="backdrop-blur-xl bg-white/[0.03] border-b border-white/10 px-8 py-4 flex items-center justify-between shrink-0 relative z-10">
        <div class="flex items-center gap-4">
            <a href="{{ route('home') }}" class="block transition hover:opacity-90">
                <img src="{{ asset('images/logo-transparent.png') }}" alt="QueueLess" class="h-11 w-auto object-contain drop-shadow-[0_2px_15px_rgba(45,212,191,0.3)]" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
            </a>
            <div class="h-8 w-px bg-white/15 hidden sm:block"></div>
            <div>
                <div class="flex items-center gap-3">
                    <span class="px-2.5 py-0.5 rounded-full bg-teal-500/20 text-teal-300 font-black text-xs border border-teal-500/30">
                        CAMPUS DISPATCH SYSTEM
                    </span>
                    @if($selectedDepartment)
                        <span class="px-3 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 font-bold text-xs border border-indigo-500/30">
                            {{ $selectedDepartment->name }} ({{ $selectedDepartment->code }})
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-400 mt-1">Enterprise High-Availability Counter Broadcast & Public Display</p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <!-- Voice Announcement Toggle -->
            <button @click="toggleVoice()" class="px-3.5 py-1.5 rounded-full border text-xs font-bold flex items-center gap-2 transition"
                    :class="voiceEnabled ? 'text-teal-300 border-teal-500/40 bg-teal-500/20' : 'text-slate-400 border-white/10 bg-white/5 hover:bg-white/10'">
                <i :data-lucide="voiceEnabled ? 'mic' : 'mic-off'" class="w-4 h-4"></i>
                <span x-text="voiceEnabled ? 'Voice Active' : 'Enable Voice'"></span>
            </button>

            <!-- Audio Chime Toggle Button -->
            <button @click="toggleAudio()" class="px-3.5 py-1.5 rounded-full border text-xs font-bold flex items-center gap-2 transition"
                    :class="audioEnabled ? 'text-emerald-300 border-emerald-500/40 bg-emerald-500/20' : 'text-slate-400 border-white/10 bg-white/5 hover:bg-white/10'">
                <i :data-lucide="audioEnabled ? 'volume-2' : 'volume-x'" class="w-4 h-4"></i>
                <span x-text="audioEnabled ? 'Chime Active' : 'Enable Chime'"></span>
            </button>

            <!-- Fullscreen Button -->
            <button @click="toggleFullscreen()" class="px-3.5 py-1.5 rounded-full border border-white/15 hover:bg-white/10 text-xs font-bold flex items-center gap-2 text-slate-300 transition">
                <i :data-lucide="isFullscreen ? 'minimize' : 'maximize'" class="w-4 h-4"></i>
                <span x-text="isFullscreen ? 'Exit Fullscreen' : 'Fullscreen'"></span>
            </button>

            <!-- Server Clock -->
            <div class="text-right pl-3 border-l border-white/10">
                <div class="text-2xl font-black font-mono text-teal-300" x-text="serverTime"></div>
                <div class="text-xs text-slate-400 font-medium" x-text="dateString"></div>
            </div>
        </div>
    </header>

    <!-- Interactive Audio Autoplay Unlock Banner for Lobby Displays -->
    <div x-show="!audioEnabled && !voiceEnabled"
         @click="toggleAudio(); toggleVoice();"
         class="cursor-pointer bg-gradient-to-r from-teal-400 via-emerald-400 to-teal-400 text-slate-950 font-black py-2.5 px-6 text-center text-xs flex items-center justify-center gap-2.5 transition hover:opacity-95 shadow-xl relative z-20">
        <span class="w-2.5 h-2.5 rounded-full bg-slate-950 animate-ping"></span>
        <span>🔊 TAP ANYWHERE ON SCREEN TO ACTIVATE LOBBY VOICE ANNOUNCEMENTS & AIRPORT CHIMES</span>
        <span class="px-2 py-0.5 rounded bg-slate-950 text-white text-[10px] font-mono font-bold">CLICK TO ACTIVATE</span>
    </div>

    <!-- Main TV Display Body -->
    <main class="flex-1 p-8 grid grid-cols-12 gap-8 overflow-hidden relative z-10">

        <!-- Left 8 cols: NOW SERVING (Large Typography) -->
        <div class="col-span-8 flex flex-col space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-3.5 h-3.5 rounded-full bg-teal-400 animate-ping"></span>
                    <h2 class="text-2xl font-black tracking-wider text-white uppercase">NOW SERVING AT COUNTERS</h2>
                </div>
                <span class="text-xs font-mono font-bold text-teal-300 bg-teal-500/10 px-3 py-1 rounded-full border border-teal-500/25" x-text="nowServing.length + ' Active Desks'"></span>
            </div>

            <!-- Cards Grid for Now Serving -->
            <div class="grid grid-cols-2 gap-5 flex-1 overflow-y-auto pr-1">
                <template x-for="item in nowServing" :key="item.ticket_id">
                    <div class="backdrop-blur-xl bg-white/[0.04] border border-teal-500/40 rounded-3xl p-6 flex flex-col justify-between shadow-2xl relative overflow-hidden transition-all duration-300 animate-token-glow">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="px-2.5 py-1 rounded-md bg-teal-500/20 text-teal-300 font-bold font-mono text-xs border border-teal-500/30" x-text="item.department_code"></span>
                                <h3 class="text-sm font-semibold text-slate-200 mt-2" x-text="item.department_name"></h3>
                                <p class="text-xs text-slate-400 mt-0.5" x-text="item.service_name"></p>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Assigned Desk</span>
                                <span class="text-xl font-black text-amber-300 font-mono" x-text="item.counter_name"></span>
                            </div>
                        </div>

                        <!-- Ticket Display -->
                        <div class="py-5 my-3 text-center bg-slate-950/70 rounded-2xl border border-white/10">
                            <span class="text-5xl sm:text-6xl font-black font-mono tracking-tight text-white drop-shadow-[0_0_25px_rgba(45,212,191,0.35)]" x-text="item.ticket_number"></span>
                        </div>

                        <div class="flex items-center justify-between text-xs text-slate-400 pt-2 border-t border-white/10">
                            <span class="flex items-center gap-1.5 text-teal-400 font-bold">
                                <i data-lucide="user-check" class="w-4 h-4"></i>
                                <span x-text="item.status"></span>
                            </span>
                            <span class="font-mono text-[11px] text-slate-300" x-text="'Called: ' + item.called_at"></span>
                        </div>
                    </div>
                </template>

                <template x-if="nowServing.length === 0">
                    <div class="col-span-2 rounded-3xl border border-white/10 bg-white/[0.02] flex flex-col items-center justify-center p-12 text-slate-400 space-y-3">
                        <i data-lucide="clock" class="w-12 h-12 text-teal-400"></i>
                        <h3 class="text-lg font-bold text-white">Counters Standing By</h3>
                        <p class="text-xs text-slate-400">Next student will be summoned shortly</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Right 4 cols: RECENTLY CALLED & DEPARTMENT SUMMARY -->
        <div class="col-span-4 flex flex-col space-y-6">

            <!-- Recently Called Card -->
            <div class="backdrop-blur-xl bg-white/[0.04] border border-white/10 rounded-3xl p-6 shadow-2xl flex-1 flex flex-col space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-white/10">
                    <h3 class="text-sm font-extrabold tracking-wider text-white uppercase flex items-center gap-2">
                        <i data-lucide="history" class="w-4 h-4 text-teal-400"></i>
                        Recently Called
                    </h3>
                    <span class="text-[10px] text-slate-400 font-mono">Latest Activity</span>
                </div>

                <div class="space-y-2.5 overflow-y-auto flex-1 pr-1">
                    <template x-for="rec in recentlyCalled" :key="rec.ticket_number + rec.time">
                        <div class="p-3 rounded-2xl bg-white/[0.03] border border-white/5 flex items-center justify-between text-xs hover:bg-white/[0.06] transition">
                            <div class="flex items-center gap-3">
                                <span class="px-2.5 py-1 rounded-xl bg-slate-950 font-mono font-black text-white text-sm border border-white/10" x-text="rec.ticket_number"></span>
                                <div>
                                    <span class="font-bold text-slate-200 block" x-text="rec.counter_name"></span>
                                    <span class="text-[10px] text-slate-400" x-text="rec.department_code"></span>
                                </div>
                            </div>
                            <span class="text-[11px] font-mono text-slate-400" x-text="rec.time"></span>
                        </div>
                    </template>

                    <template x-if="recentlyCalled.length === 0">
                        <div class="py-12 text-center text-xs text-slate-500">
                            No recent tickets in history today.
                        </div>
                    </template>
                </div>
            </div>

            <!-- Department Queue Status Bar -->
            <div class="backdrop-blur-xl bg-white/[0.04] border border-white/10 rounded-3xl p-5 shadow-2xl space-y-3 shrink-0">
                <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Campus Queue Activity</h4>
                <div class="grid grid-cols-3 gap-2 text-center">
                    <template x-for="d in departments.slice(0, 6)" :key="d.code">
                        <div class="p-2.5 rounded-xl bg-white/[0.03] border border-white/5">
                            <span class="text-[11px] font-mono font-bold text-slate-300 block" x-text="d.code"></span>
                            <span class="text-sm font-black font-mono text-teal-300" x-text="d.waiting"></span>
                            <span class="text-[9px] text-slate-400 uppercase block">waiting</span>
                        </div>
                    </template>
                </div>
            </div>

        </div>

    </main>

    <!-- Footer Marquee Announcement -->
    <footer class="backdrop-blur-xl bg-white/[0.03] border-t border-white/10 px-8 py-3 flex items-center justify-between text-xs text-slate-400 shrink-0 relative z-10">
        <div class="flex items-center gap-3">
            <span class="px-2 py-0.5 rounded-full bg-teal-500/20 text-teal-300 font-black text-[10px] uppercase border border-teal-500/30">Announcement</span>
            <span class="text-slate-300">
                Please hold your digital token and proceed promptly when your ticket is summoned. For live smartphone queue tracking, visit: <strong class="text-teal-300 font-mono">{{ url('/') }}</strong>
            </span>
        </div>
        <div class="text-[11px] font-mono text-slate-500 hidden md:block">
            QueueLess • Intelligent High-Availability Digital Queue Infrastructure
        </div>
    </footer>

    <script>
    function tvSignage() {
        return {
            deptCode: '{{ $departmentCode ?? '' }}',
            nowServing: [],
            recentlyCalled: [],
            departments: [],
            serverTime: '{{ now()->format('h:i:s A') }}',
            dateString: '{{ now()->format('l, F j, Y') }}',
            audioEnabled: false,
            voiceEnabled: false,
            isFullscreen: false,
            lastCalledTokens: [],

            init() {
                lucide.createIcons();
                this.pollData();

                // Live poll every 2.0s
                setInterval(() => {
                    this.pollData();
                }, 2000);

                // Local clock tick
                setInterval(() => {
                    const now = new Date();
                    this.serverTime = now.toLocaleTimeString();
                }, 1000);

                document.addEventListener('fullscreenchange', () => {
                    this.isFullscreen = !!document.fullscreenElement;
                    this.$nextTick(() => lucide.createIcons());
                });
            },

            toggleAudio() {
                this.audioEnabled = !this.audioEnabled;
                if (this.audioEnabled) {
                    this.playAirportChime();
                }
                this.$nextTick(() => lucide.createIcons());
            },

            toggleVoice() {
                this.voiceEnabled = !this.voiceEnabled;
                if (this.voiceEnabled) {
                    this.speakAnnouncement("Voice announcements active");
                }
                this.$nextTick(() => lucide.createIcons());
            },

            toggleFullscreen() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(err => {
                        console.warn("Fullscreen request error:", err);
                    });
                } else {
                    document.exitFullscreen().catch(err => {
                        console.warn("Exit fullscreen error:", err);
                    });
                }
            },

            playAirportChime() {
                if (!this.audioEnabled) return;
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const now = ctx.currentTime;

                    const playTone = (freq, start, duration) => {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(freq, start);
                        gain.gain.setValueAtTime(0.3, start);
                        gain.gain.exponentialRampToValueAtTime(0.001, start + duration);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start(start);
                        osc.stop(start + duration);
                    };

                    // Airport 2-tone chime: F5 (698.46Hz) then C6 (1046.50Hz)
                    playTone(698.46, now, 0.4);
                    playTone(1046.50, now + 0.35, 0.8);
                } catch (e) {
                    console.log("Audio play error", e);
                }
            },

            speakAnnouncement(text) {
                if (!('speechSynthesis' in window)) return;
                window.speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.rate = 0.95;
                utterance.pitch = 1.0;
                utterance.lang = 'en-US';
                window.speechSynthesis.speak(utterance);
            },

            speakToken(ticketNumber, counterName) {
                if (!this.voiceEnabled || !('speechSynthesis' in window)) return;
                window.speechSynthesis.cancel();
                const formatted = ticketNumber.split('-').join(' ');
                const utterance = new SpeechSynthesisUtterance(`Token ${formatted}, please proceed to ${counterName}`);
                utterance.rate = 0.9;
                utterance.pitch = 1.0;
                utterance.lang = 'en-US';
                window.speechSynthesis.speak(utterance);
            },

            async pollData() {
                try {
                    const url = this.deptCode ? `/api/display/live/${this.deptCode}` : '/api/display/live';
                    const res = await fetch(url);
                    if (!res.ok) return;
                    const json = await res.json();
                    if (json.success) {
                        const newServing = json.data.now_serving || [];

                        // Check if a new token was called
                        if (newServing.length > 0 && this.lastCalledTokens.length > 0) {
                            const latest = newServing[0];
                            if (!this.lastCalledTokens.includes(latest.ticket_number)) {
                                this.playAirportChime();
                                if (this.voiceEnabled) {
                                    setTimeout(() => {
                                        this.speakToken(latest.ticket_number, latest.counter_name);
                                    }, 600);
                                }
                            }
                        }

                        this.lastCalledTokens = newServing.map(t => t.ticket_number);
                        this.nowServing = newServing;
                        this.recentlyCalled = json.data.recently_called || [];
                        this.departments = json.data.departments || [];
                        this.dateString = json.data.date_string;

                        this.$nextTick(() => lucide.createIcons());
                    }
                } catch (err) {
                    console.error("Signage sync error:", err);
                }
            }
        }
    }
    </script>
</body>
</html>
