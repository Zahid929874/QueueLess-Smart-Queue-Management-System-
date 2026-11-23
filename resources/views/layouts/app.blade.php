<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full antialiased selection:bg-[#00c389] selection:text-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'QueueLess' }} — Smart Campus Queue Terminal</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Fonts: Plus Jakarta Sans, Outfit & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Theme Initialization (Prevents Flash of Unstyled Content) -->
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('queueless_theme');
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                    document.documentElement.classList.add('dark');
                    document.documentElement.classList.remove('light');
                } else {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.classList.add('light');
                }
            } catch (e) {}
        })();
    </script>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Outfit', 'Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        mint: {
                            50: '#f0fdf9',
                            100: '#ccfbef',
                            200: '#99f6df',
                            300: '#5eead4',
                            400: '#2dd4bf',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                            accent: '#00c389',
                        },
                        terracotta: {
                            900: '#1f1311',
                            800: '#2d1b18',
                            700: '#3e2520',
                            600: '#52312b',
                            500: '#8c5349',
                            400: '#b87366',
                        }
                    },
                    boxShadow: {
                        'canvas': '0 30px 90px -15px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.15)',
                        'card': '0 8px 30px -4px rgba(0, 0, 0, 0.04), 0 2px 6px -1px rgba(0, 0, 0, 0.02)',
                        'card-hover': '0 20px 40px -8px rgba(0, 195, 137, 0.12), 0 8px 20px -4px rgba(0, 0, 0, 0.06)',
                        'mint': '0 10px 25px -5px rgba(0, 195, 137, 0.35)',
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Chart.js for Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .font-mono-num { font-variant-numeric: tabular-nums; }

        /* Smooth Theme Switch Transitions */
        html {
            transition: background-color 0.3s cubic-bezier(0.16, 1, 0.3, 1), color 0.2s ease;
        }
        
        /* Full Bright Light Theme Radiant Ambient Backdrop */
        html:not(.dark) .silk-backdrop {
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 10% 12%, rgba(0, 195, 137, 0.12) 0px, transparent 40%),
                radial-gradient(at 90% 15%, rgba(249, 115, 22, 0.08) 0px, transparent 45%),
                radial-gradient(at 80% 85%, rgba(14, 165, 233, 0.08) 0px, transparent 50%),
                radial-gradient(at 15% 85%, rgba(168, 85, 247, 0.06) 0px, transparent 45%),
                linear-gradient(to bottom, #ffffff, #f1f5f9);
            background-attachment: fixed;
        }

        /* Full Dark Obsidian Ambient Backdrop */
        html.dark .silk-backdrop {
            background-color: #070b14;
            background-image: 
                radial-gradient(at 15% 15%, rgba(0, 195, 137, 0.14) 0px, transparent 45%),
                radial-gradient(at 85% 20%, rgba(14, 165, 233, 0.12) 0px, transparent 50%),
                radial-gradient(at 75% 85%, rgba(99, 102, 241, 0.14) 0px, transparent 50%),
                radial-gradient(at 20% 80%, rgba(184, 115, 102, 0.15) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(7, 11, 20, 0.98) 0px, transparent 75%);
            background-attachment: fixed;
        }

        /* Subtle Striped Pattern for Capsule Bars */
        .striped-bar-pattern {
            background-image: repeating-linear-gradient(
                -45deg,
                rgba(0, 195, 137, 0.15),
                rgba(0, 195, 137, 0.15) 6px,
                rgba(0, 195, 137, 0.05) 6px,
                rgba(0, 195, 137, 0.05) 12px
            );
        }

        /* Custom Hover & Interactive Transitions */
        .interactive-card {
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .interactive-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -8px rgba(0, 195, 137, 0.12), 0 8px 20px -4px rgba(0, 0, 0, 0.06);
            border-color: rgba(0, 195, 137, 0.4);
        }
        html.dark .interactive-card:hover {
            box-shadow: 0 20px 40px -8px rgba(0, 195, 137, 0.22), 0 8px 25px -4px rgba(0, 0, 0, 0.5);
            border-color: rgba(0, 195, 137, 0.5);
        }

        /* Smooth Progress Bar Slider Track */
        .slider-track {
            position: relative;
            height: 6px;
            border-radius: 9999px;
            background: #f1f5f9;
            overflow: visible;
        }
        html.dark .slider-track {
            background: #1e293b;
        }
        .slider-fill {
            height: 100%;
            border-radius: 9999px;
            background: #00c389;
            transition: width 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .slider-knob {
            position: absolute;
            top: 50%;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #ffffff;
            border: 2.5px solid #00c389;
            transform: translate(-50%, -50%);
            box-shadow: 0 2px 6px rgba(0, 195, 137, 0.3);
            transition: left 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }
        html.dark .slider-knob {
            background: #0f172a;
            border-color: #00c389;
            box-shadow: 0 0 10px rgba(0, 195, 137, 0.5);
        }

        /* Concentric Rings Pulse */
        @keyframes ring-glow {
            0%, 100% { transform: scale(1); opacity: 0.95; }
            50% { transform: scale(1.02); opacity: 1; }
        }
        .animate-ring-pulse {
            animation: ring-glow 3s ease-in-out infinite;
        }

        /* Holographic Shimmer Effect for Digital Pass */
        .holographic-sheen {
            background: linear-gradient(
                135deg,
                rgba(255, 255, 255, 0.05) 0%,
                rgba(0, 195, 137, 0.15) 25%,
                rgba(194, 89, 63, 0.15) 50%,
                rgba(6, 182, 212, 0.15) 75%,
                rgba(255, 255, 255, 0.05) 100%
            );
            background-size: 300% 300%;
            animation: holoShift 6s ease infinite;
        }
        @keyframes holoShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Paper Receipt Physical Tear */
        .paper-receipt {
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25), 0 0 0 1px rgba(0,0,0,0.06);
            background-image: repeating-linear-gradient(
                0deg,
                rgba(0, 0, 0, 0.015),
                rgba(0, 0, 0, 0.015) 1px,
                transparent 1px,
                transparent 2px
            );
        }

        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
        }
        .print-only { display: none; }
    </style>
</head>
<body class="min-h-full flex flex-col font-sans silk-backdrop text-slate-800 dark:text-slate-100 antialiased p-3 sm:p-6 lg:p-10 select-none"
      x-data="{
          mobileMenu: false,
          hotkeysModal: false,
          commandPaletteOpen: false,
          aiDrawerOpen: false,
          soundProfileModal: false,
          soundProfile: localStorage.getItem('queueless_sound_profile') || 'airport',
          lang: localStorage.getItem('queueless_lang') || 'en',
          theme: localStorage.getItem('queueless_theme') || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),
          searchQuery: '',
          activeNav: '{{ request()->routeIs('home') ? 'dashboard' : (request()->routeIs('student.join') ? 'join' : (request()->routeIs('student.dashboard') ? 'ticket' : (request()->routeIs('student.history') ? 'history' : 'dashboard'))) }}',
          soundEnabled: localStorage.getItem('queueless_sound') !== 'false',
          toasts: [],

          toggleTheme() {
              this.theme = this.theme === 'dark' ? 'light' : 'dark';
              localStorage.setItem('queueless_theme', this.theme);
              if (this.theme === 'dark') {
                  document.documentElement.classList.add('dark');
                  document.documentElement.classList.remove('light');
              } else {
                  document.documentElement.classList.remove('dark');
                  document.documentElement.classList.add('light');
              }
              if (window.playTactileSound) window.playTactileSound('click');
              this.$nextTick(() => {
                  if (window.lucide) lucide.createIcons();
              });
              this.addToast(this.theme === 'dark' ? 'Obsidian Dark theme activated' : 'Radiant Bright Light theme activated', 'info');
          },

          toggleLang() {
              this.lang = this.lang === 'en' ? 'bn' : 'en';
              localStorage.setItem('queueless_lang', this.lang);
              this.addToast(this.lang === 'bn' ? 'ভাষা বাংলা নির্বাচন করা হয়েছে' : 'Language set to English', 'info');
          },

          setSoundProfile(profile) {
              this.soundProfile = profile;
              localStorage.setItem('queueless_sound_profile', profile);
              if (profile === 'mute') {
                  this.soundEnabled = false;
                  localStorage.setItem('queueless_sound', 'false');
              } else {
                  this.soundEnabled = true;
                  localStorage.setItem('queueless_sound', 'true');
                  if (window.playTactileSound) window.playTactileSound('chime');
              }
              this.soundProfileModal = false;
              this.addToast('Sound profile: ' + profile.toUpperCase(), 'success');
          },

          toggleSound() {
              this.soundEnabled = !this.soundEnabled;
              localStorage.setItem('queueless_sound', this.soundEnabled);
              if (this.soundEnabled && window.playTactileSound) {
                  window.playTactileSound('click');
              }
              this.addToast(this.soundEnabled ? 'Audio cues enabled' : 'Audio muted', 'info');
          },

          addToast(message, type = 'success') {
              const id = Date.now();
              this.toasts.push({ id, message, type });
              if (window.playTactileSound && this.soundEnabled) {
                  window.playTactileSound(type === 'error' ? 'click' : 'chime');
              }
              setTimeout(() => {
                  this.toasts = this.toasts.filter(t => t.id !== id);
              }, 4000);
          }
      }"
      @keydown.window="
          // Hotkeys handler
          if (($event.ctrlKey || $event.metaKey) && $event.key.toLowerCase() === 'k') {
              $event.preventDefault();
              commandPaletteOpen = !commandPaletteOpen;
              if (commandPaletteOpen) $nextTick(() => { document.getElementById('paletteSearchInput')?.focus(); });
          }
          if (($event.ctrlKey || $event.metaKey) && $event.key.toLowerCase() === 'j') {
              $event.preventDefault();
              aiDrawerOpen = !aiDrawerOpen;
              if (aiDrawerOpen && window.playTactileSound) window.playTactileSound('click');
          }
          if ($event.key === '?' && !['INPUT', 'TEXTAREA'].includes($event.target.tagName)) {
              $event.preventDefault();
              hotkeysModal = !hotkeysModal;
          }
          if ($event.key === 'Escape') {
              hotkeysModal = false;
              commandPaletteOpen = false;
              aiDrawerOpen = false;
              soundProfileModal = false;
          }
      "
      x-init="
          window.showToast = (msg, type) => addToast(msg, type);
          window.openAiDrawer = () => { aiDrawerOpen = true; };
          window.openCommandPalette = () => { commandPaletteOpen = true; $nextTick(() => { document.getElementById('paletteSearchInput')?.focus(); }); };
          window.addEventListener('offline', () => addToast('Network disconnected. Retrying campus link...', 'error'));
          window.addEventListener('online', () => addToast('Campus link restored. Live queue online.', 'success'));
          @if(session('success')) addToast(@json(session('success')), 'success'); @endif
          @if(session('error')) addToast(@json(session('error')), 'error'); @endif
      ">

    <!-- Native Web Audio Soundscape Engine (Multi-Profile Synthesizer) -->
    <script>
        (function() {
            let audioCtx = null;
            function getCtx() {
                if (!audioCtx) {
                    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
                return audioCtx;
            }

            window.playTactileSound = function(type) {
                try {
                    const soundState = localStorage.getItem('queueless_sound');
                    if (soundState === 'false') return;
                    const profile = localStorage.getItem('queueless_sound_profile') || 'airport';
                    if (profile === 'mute') return;

                    const ctx = getCtx();
                    const now = ctx.currentTime;

                    if (type === 'click') {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = profile === 'scifi' ? 'sawtooth' : 'sine';
                        osc.frequency.setValueAtTime(profile === 'scifi' ? 1800 : 1400, now);
                        osc.frequency.exponentialRampToValueAtTime(300, now + 0.03);
                        gain.gain.setValueAtTime(0.08, now);
                        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.03);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start(now);
                        osc.stop(now + 0.03);
                    } else if (type === 'dispense') {
                        for (let i = 0; i < 5; i++) {
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            const t = now + (i * 0.05);
                            osc.type = 'triangle';
                            osc.frequency.setValueAtTime(260 + (i * 45), t);
                            gain.gain.setValueAtTime(0.06, t);
                            gain.gain.exponentialRampToValueAtTime(0.001, t + 0.04);
                            osc.connect(gain);
                            gain.connect(ctx.destination);
                            osc.start(t);
                            osc.stop(t + 0.04);
                        }
                    } else if (type === 'chime') {
                        if (profile === 'scifi') {
                            // Futuristic laser-harmonic sweep
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            osc.type = 'sine';
                            osc.frequency.setValueAtTime(520, now);
                            osc.frequency.exponentialRampToValueAtTime(1480, now + 0.35);
                            gain.gain.setValueAtTime(0.18, now);
                            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
                            osc.connect(gain);
                            gain.connect(ctx.destination);
                            osc.start(now);
                            osc.stop(now + 0.5);
                        } else {
                            // Classic Airport Dual-Tone Chime: D5 (587.33Hz) -> A5 (880.00Hz)
                            const playTone = (freq, start, duration) => {
                                const osc = ctx.createOscillator();
                                const gain = ctx.createGain();
                                osc.type = 'sine';
                                osc.frequency.setValueAtTime(freq, start);
                                gain.gain.setValueAtTime(0.18, start);
                                gain.gain.exponentialRampToValueAtTime(0.001, start + duration);
                                osc.connect(gain);
                                gain.connect(ctx.destination);
                                osc.start(start);
                                osc.stop(start + duration);
                            };
                            playTone(587.33, now, 0.35);
                            playTone(880.00, now + 0.28, 0.7);
                        }
                    } else if (type === 'nfc') {
                        // High-tech NFC validation chime
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(880, now);
                        osc.frequency.setValueAtTime(1320, now + 0.08);
                        gain.gain.setValueAtTime(0.12, now);
                        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.25);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start(now);
                        osc.stop(now + 0.25);
                    }
                } catch(e) {}
            };
        })();
    </script>

    <!-- Floating Toast Notification -->
    <div class="fixed top-6 right-6 z-50 space-y-2 pointer-events-none w-80 max-w-[calc(100vw-3rem)]">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="pointer-events-auto p-4 rounded-2xl shadow-xl border text-xs font-semibold flex items-center justify-between gap-3 transform transition-all duration-300"
                 :class="{
                     'bg-white text-slate-900 border-mint-accent/40 shadow-mint': toast.type === 'success',
                     'bg-white text-slate-900 border-rose-400': toast.type === 'error',
                     'bg-white text-slate-900 border-indigo-400': toast.type === 'info'
                 }">
                <div class="flex items-center gap-3">
                    <span class="w-2.5 h-2.5 rounded-full"
                          :class="{
                              'bg-mint-accent animate-ping': toast.type === 'success',
                              'bg-rose-500 animate-ping': toast.type === 'error',
                              'bg-indigo-500 animate-ping': toast.type === 'info'
                          }"></span>
                    <span x-text="toast.message" class="leading-tight font-medium"></span>
                </div>
                <button @click="toasts = toasts.filter(t => t.id !== toast.id)" class="text-slate-400 hover:text-slate-700 shrink-0">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        </template>
    </div>

    <!-- Pinned Top Ribbon (Universal Controls, Personas, Sound & Command Palette) -->
    <aside aria-label="Persona switcher" class="no-print max-w-7xl mx-auto w-full mb-3 px-2 flex items-center justify-between text-xs text-slate-700 dark:text-white/80">
        <div class="flex items-center gap-2 font-mono text-[11px]">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-slate-900/10 dark:bg-white/10 backdrop-blur-md text-[#00c389] font-bold border border-slate-300/60 dark:border-white/10">
                <span class="w-1.5 h-1.5 rounded-full bg-[#00c389] animate-pulse"></span>
                <span x-text="lang === 'bn' ? 'সরাসরি ক্যাম্পাস ডিসপ্যাচ টার্মিনাল' : 'LIVE DISPATCH TERMINAL'"></span>
            </span>
            <span class="text-slate-500 dark:text-white/60 hidden sm:inline">• BAUST Smart Operations</span>
        </div>

        <div class="flex items-center gap-2 overflow-x-auto text-[11px]">
            <!-- Command Palette Quick Launcher -->
            <button @click="commandPaletteOpen = true; $nextTick(() => { document.getElementById('paletteSearchInput')?.focus(); })"
                    class="px-2.5 py-1 rounded-full bg-slate-900/10 dark:bg-white/10 hover:bg-slate-900/15 dark:hover:bg-white/20 backdrop-blur-md text-slate-800 dark:text-white border border-slate-300/60 dark:border-white/10 font-mono font-bold transition flex items-center gap-1 shadow-2xs"
                    title="Press Ctrl+K to search">
                <i data-lucide="command" class="w-3.5 h-3.5 text-[#00c389]"></i>
                <span class="hidden sm:inline">Search</span>
                <kbd class="px-1 py-0.2 rounded bg-black/10 dark:bg-black/30 text-[9px] text-slate-700 dark:text-white/70">⌘K</kbd>
            </button>

            <!-- AI QueueBot Launcher -->
            <button @click="aiDrawerOpen = true; if (window.playTactileSound) window.playTactileSound('click')"
                    class="px-2.5 py-1 rounded-full bg-gradient-to-r from-mint-500/20 to-emerald-500/10 dark:from-mint-500/30 dark:to-emerald-500/20 hover:from-mint-500/30 text-[#00c389] border border-[#00c389]/40 font-mono font-bold transition flex items-center gap-1.5 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-[#00c389] animate-ping"></span>
                <span>QueueBot AI</span>
            </button>

            <!-- Theme Mode Switcher Pill -->
            <button @click="toggleTheme()"
                    class="px-2.5 py-1 rounded-full backdrop-blur-md font-mono font-bold transition flex items-center gap-1.5 shadow-2xs border"
                    :class="theme === 'dark' ? 'bg-amber-400/20 border-amber-400/40 text-amber-300 hover:bg-amber-400/30' : 'bg-slate-900/10 border-slate-300/70 text-slate-800 hover:bg-slate-900/15'"
                    :title="theme === 'dark' ? 'Switch to Radiant Bright Light Mode' : 'Switch to Obsidian Dark Mode'">
                <i :data-lucide="theme === 'dark' ? 'sun' : 'moon'" class="w-3.5 h-3.5" :class="theme === 'dark' ? 'text-amber-400' : 'text-indigo-600'"></i>
                <span class="hidden sm:inline" x-text="theme === 'dark' ? 'LIGHT' : 'DARK'"></span>
            </button>

            <div class="h-3 w-px bg-slate-300/70 dark:bg-white/20 hidden sm:block"></div>

            <!-- Sound Profile Selector -->
            <button @click="soundProfileModal = true"
                    class="px-2.5 py-1 rounded-full bg-slate-900/10 dark:bg-white/10 hover:bg-slate-900/15 dark:hover:bg-white/20 backdrop-blur-md text-slate-800 dark:text-white border border-slate-300/60 dark:border-white/10 font-mono font-bold transition flex items-center gap-1.5">
                <i :data-lucide="soundEnabled ? 'volume-2' : 'volume-x'" class="w-3.5 h-3.5 text-[#00c389]"></i>
                <span class="hidden sm:inline" x-text="soundProfile.toUpperCase()"></span>
            </button>

            <!-- Bilingual Toggle -->
            <button @click="toggleLang()"
                    class="px-2 py-1 rounded-full bg-slate-900/10 dark:bg-white/10 hover:bg-slate-900/15 dark:hover:bg-white/20 backdrop-blur-md text-slate-800 dark:text-white border border-slate-300/60 dark:border-white/10 font-mono font-bold transition flex items-center gap-1"
                    title="Switch Language">
                <i data-lucide="languages" class="w-3.5 h-3.5 text-amber-500 dark:text-amber-400"></i>
                <span x-text="lang === 'bn' ? 'বাংলা' : 'EN'"></span>
            </button>

            <div class="h-3 w-px bg-slate-300/70 dark:bg-white/20 hidden sm:block"></div>

            <!-- Personas -->
            <a href="{{ route('student.login') }}" @click="window.playTactileSound('click')" class="px-2.5 py-1 rounded-full bg-slate-900/10 dark:bg-white/10 hover:bg-slate-900/15 dark:hover:bg-white/20 backdrop-blur-md text-slate-800 dark:text-white border border-slate-300/60 dark:border-white/10 font-mono transition flex items-center gap-1">
                👤 Student
            </a>
            <a href="{{ route('operator.login') }}" @click="window.playTactileSound('click')" class="px-2.5 py-1 rounded-full bg-slate-900/10 dark:bg-white/10 hover:bg-slate-900/15 dark:hover:bg-white/20 backdrop-blur-md text-slate-800 dark:text-white border border-slate-300/60 dark:border-white/10 font-mono transition flex items-center gap-1">
                🎯 Operator
            </a>
            <a href="{{ route('admin.login') }}" @click="window.playTactileSound('click')" class="px-2.5 py-1 rounded-full bg-amber-500/20 hover:bg-amber-500/30 text-amber-800 dark:text-amber-200 border border-amber-500/40 font-mono transition flex items-center gap-1">
                ⚡ Admin
            </a>
            <a href="{{ route('public.display') }}" target="_blank" class="px-2.5 py-1 rounded-full bg-slate-900/10 dark:bg-white/10 hover:bg-slate-900/15 dark:hover:bg-white/20 backdrop-blur-md text-rose-600 dark:text-rose-300 border border-slate-300/60 dark:border-white/10 font-mono transition flex items-center gap-1">
                <i data-lucide="tv" class="w-3 h-3 text-rose-500 dark:text-rose-400"></i> TV <i data-lucide="external-link" class="w-2.5 h-2.5 ml-0.5 opacity-60"></i>
            </a>
        </div>
    </aside>

    <!-- ======================================================== -->
    <!-- UNIVERSAL COMMAND PALETTE (Cmd+K / Ctrl+K)                -->
    <!-- ======================================================== -->
    <div x-show="commandPaletteOpen" x-cloak class="fixed inset-0 z-50 flex items-start justify-center pt-20 bg-slate-950/70 backdrop-blur-md p-4">
        <div @click.away="commandPaletteOpen = false" class="bg-white dark:bg-[#0c1322] border border-slate-200 dark:border-slate-800 rounded-3xl max-w-2xl w-full shadow-2xl overflow-hidden text-slate-900 dark:text-slate-100 transition-all transform scale-100">
            <!-- Search Input Header -->
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3 bg-slate-50/50 dark:bg-[#10192a]">
                <i data-lucide="search" class="w-5 h-5 text-slate-400"></i>
                <input id="paletteSearchInput"
                       type="text"
                       x-model="searchQuery"
                       placeholder="Type a command, department, action or keyword... (Esc to close)"
                       class="w-full bg-transparent text-sm font-semibold text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none">
                <kbd class="px-2 py-0.5 rounded bg-slate-200/80 dark:bg-slate-800 text-[10px] font-mono text-slate-600 dark:text-slate-300 font-bold">ESC</kbd>
            </div>

            <!-- Palette Results Body -->
            <div class="max-h-96 overflow-y-auto p-3 space-y-4 text-xs">
                <!-- Group 1: Fast Navigation & Views -->
                <div class="space-y-1">
                    <span class="px-3 text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400">Views & Portals</span>
                    <a href="{{ route('home') }}" @click="commandPaletteOpen = false" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-mint-50 dark:hover:bg-[#142034] hover:text-mint-900 dark:hover:text-white transition group">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-mint-100 dark:bg-mint-950/50 text-[#00c389] flex items-center justify-center font-bold">
                                <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                            </span>
                            <div>
                                <span class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-mint-900 dark:group-hover:text-white block">Campus Operations Dashboard</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">Real-time campus queue telemetry</span>
                            </div>
                        </div>
                        <kbd class="font-mono text-[10px] text-slate-400">Jump ↵</kbd>
                    </a>

                    <a href="{{ route('student.dashboard') }}" @click="commandPaletteOpen = false" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-mint-50 dark:hover:bg-[#142034] hover:text-mint-900 dark:hover:text-white transition group">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                                <i data-lucide="ticket" class="w-3.5 h-3.5"></i>
                            </span>
                            <div>
                                <span class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-indigo-900 dark:group-hover:text-white block">Student Boarding Pass Tracker</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">Live ticket tracker and countdown</span>
                            </div>
                        </div>
                        <kbd class="font-mono text-[10px] text-slate-400">Jump ↵</kbd>
                    </a>

                    <a href="{{ route('operator.console') }}" @click="commandPaletteOpen = false" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-mint-50 dark:hover:bg-[#142034] hover:text-mint-900 dark:hover:text-white transition group">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400 flex items-center justify-center font-bold">
                                <i data-lucide="headset" class="w-3.5 h-3.5"></i>
                            </span>
                            <div>
                                <span class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-amber-900 dark:group-hover:text-white block">Counter Console</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">Hotkey desk summoning workstation</span>
                            </div>
                        </div>
                        <kbd class="font-mono text-[10px] text-slate-400">Jump ↵</kbd>
                    </a>

                    <a href="{{ route('admin.dashboard') }}" @click="commandPaletteOpen = false" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-mint-50 dark:hover:bg-[#142034] hover:text-mint-900 dark:hover:text-white transition group">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-rose-100 dark:bg-rose-950/50 text-rose-700 dark:text-rose-400 flex items-center justify-center font-bold">
                                <i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i>
                            </span>
                            <div>
                                <span class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-rose-900 dark:group-hover:text-white block">Admin Capacity & Congestion Matrix</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">Erlang-C auto-scaling & staff allocation</span>
                            </div>
                        </div>
                        <kbd class="font-mono text-[10px] text-slate-400">Jump ↵</kbd>
                    </a>

                    <a href="{{ route('public.display') }}" target="_blank" @click="commandPaletteOpen = false" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-mint-50 dark:hover:bg-[#142034] hover:text-mint-900 dark:hover:text-white transition group">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-slate-900 text-rose-400 flex items-center justify-center font-bold">
                                <i data-lucide="tv" class="w-3.5 h-3.5"></i>
                            </span>
                            <div>
                                <span class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-slate-900 dark:group-hover:text-white block">4K Public Lobby Digital Signage</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">Cinema-grade lobby TV monitor with audio chime</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-mono text-rose-500 font-bold">New Window ↗</span>
                    </a>
                </div>

                <!-- Group 2: AI & Interactive Tools -->
                <div class="space-y-1 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <span class="px-3 text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400">AI & Interactive Features</span>
                    
                    <!-- Theme Toggle Option in Palette -->
                    <button @click="toggleTheme(); commandPaletteOpen = false" class="w-full flex items-center justify-between p-2.5 rounded-xl hover:bg-mint-50 dark:hover:bg-[#142034] text-left transition group">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
                                <i data-lucide="sun-moon" class="w-3.5 h-3.5"></i>
                            </span>
                            <div>
                                <span class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-amber-900 dark:group-hover:text-white block">Toggle Dark / Light Theme</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">Currently: <strong x-text="theme === 'dark' ? 'Full Obsidian Dark' : 'Radiant Bright Light'" class="text-[#00c389]"></strong></span>
                            </div>
                        </div>
                        <span class="text-[10px] font-mono text-amber-600 dark:text-amber-400 font-bold" x-text="theme.toUpperCase()"></span>
                    </button>

                    <button @click="commandPaletteOpen = false; aiDrawerOpen = true" class="w-full flex items-center justify-between p-2.5 rounded-xl hover:bg-mint-50 dark:hover:bg-[#142034] text-left transition group">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-mint-500 text-white flex items-center justify-center font-bold shadow-xs">
                                <i data-lucide="bot" class="w-3.5 h-3.5"></i>
                            </span>
                            <div>
                                <span class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-mint-900 dark:group-hover:text-white block">Open QueueBot AI Assistant</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">Ask questions, check queue times, or get documents info</span>
                            </div>
                        </div>
                        <kbd class="font-mono text-[10px] text-slate-400">⌘J</kbd>
                    </button>

                    <button @click="commandPaletteOpen = false; window.playTactileSound('chime')" class="w-full flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-[#142034] text-left transition group">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-teal-100 dark:bg-teal-950/50 text-teal-700 dark:text-teal-400 flex items-center justify-center font-bold">
                                <i data-lucide="volume-2" class="w-3.5 h-3.5"></i>
                            </span>
                            <div>
                                <span class="font-bold text-slate-800 dark:text-slate-200 block">Test Airport Dual-Tone Audio Chime</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">Web Audio API (587.33 Hz → 880.00 Hz)</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-mono text-teal-600 dark:text-teal-400 font-bold">Play ♪</span>
                    </button>

                    <a href="{{ route('admin.export-csv') }}" @click="commandPaletteOpen = false" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-[#142034] transition group">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 flex items-center justify-center font-bold">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            </span>
                            <div>
                                <span class="font-bold text-slate-800 dark:text-slate-200 block">Download Audit Records (CSV)</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">Accreditation and institutional export</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-mono text-emerald-700 dark:text-emerald-400 font-bold">Export ⤓</span>
                    </a>
                </div>
            </div>

            <!-- Palette Footer -->
            <div class="p-3 bg-slate-50 dark:bg-[#10192a] border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-400 dark:text-slate-500 font-mono">
                <span>Navigation: <kbd class="px-1.5 py-0.5 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">↑</kbd> <kbd class="px-1.5 py-0.5 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">↓</kbd> to move</span>
                <span>Press <kbd class="px-1.5 py-0.5 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">Enter</kbd> to select</span>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- SOUND PROFILE SELECTOR MODAL                             -->
    <!-- ======================================================== -->
    <div x-show="soundProfileModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4">
        <div @click.away="soundProfileModal = false" class="bg-white dark:bg-[#0c1322] border border-slate-200 dark:border-slate-800 rounded-3xl max-w-sm w-full p-6 shadow-2xl text-slate-900 dark:text-slate-100 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-mint-50 dark:bg-mint-950/50 text-[#00c389] flex items-center justify-center font-bold">
                        <i data-lucide="sliders" class="w-4 h-4"></i>
                    </div>
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white font-display">Audio Synthesizer Profile</h3>
                </div>
                <button @click="soundProfileModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="space-y-2 text-xs">
                <button @click="setSoundProfile('airport')"
                        :class="soundProfile === 'airport' ? 'bg-mint-50 dark:bg-mint-950/40 border-[#00c389] text-mint-950 dark:text-mint-200 font-bold' : 'bg-slate-50 dark:bg-[#142034] border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300'"
                        class="w-full flex items-center justify-between p-3 rounded-2xl border transition text-left">
                    <div>
                        <span class="block font-bold">Airport Chime (Default)</span>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500">Harmonic dual-tone chime (587.33 Hz → 880.00 Hz)</span>
                    </div>
                    <i x-show="soundProfile === 'airport'" data-lucide="check" class="w-4 h-4 text-[#00c389]"></i>
                </button>

                <button @click="setSoundProfile('scifi')"
                        :class="soundProfile === 'scifi' ? 'bg-mint-50 dark:bg-mint-950/40 border-[#00c389] text-mint-950 dark:text-mint-200 font-bold' : 'bg-slate-50 dark:bg-[#142034] border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300'"
                        class="w-full flex items-center justify-between p-3 rounded-2xl border transition text-left">
                    <div>
                        <span class="block font-bold">Cyberpunk / Sci-Fi Pulse</span>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500">Futuristic frequency sweep with high resonance</span>
                    </div>
                    <i x-show="soundProfile === 'scifi'" data-lucide="check" class="w-4 h-4 text-[#00c389]"></i>
                </button>

                <button @click="setSoundProfile('tactile')"
                        :class="soundProfile === 'tactile' ? 'bg-mint-50 dark:bg-mint-950/40 border-[#00c389] text-mint-950 dark:text-mint-200 font-bold' : 'bg-slate-50 dark:bg-[#142034] border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300'"
                        class="w-full flex items-center justify-between p-3 rounded-2xl border transition text-left">
                    <div>
                        <span class="block font-bold">Subtle Haptic Clicks</span>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500">Soft tactile feedback without loud chimes</span>
                    </div>
                    <i x-show="soundProfile === 'tactile'" data-lucide="check" class="w-4 h-4 text-[#00c389]"></i>
                </button>

                <button @click="setSoundProfile('mute')"
                        :class="soundProfile === 'mute' ? 'bg-rose-50 dark:bg-rose-950/40 border-rose-400 text-rose-950 dark:text-rose-200 font-bold' : 'bg-slate-50 dark:bg-[#142034] border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300'"
                        class="w-full flex items-center justify-between p-3 rounded-2xl border transition text-left">
                    <div>
                        <span class="block font-bold">Mute All Audio</span>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500">Silent operation for quiet office spaces</span>
                    </div>
                    <i x-show="soundProfile === 'mute'" data-lucide="check" class="w-4 h-4 text-rose-500"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Global Keyboard Hotkey Modal Dialog -->
    <div x-show="hotkeysModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4">
        <div @click.away="hotkeysModal = false" class="bg-white dark:bg-[#0c1322] border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl text-slate-900 dark:text-slate-100 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-mint-50 dark:bg-mint-950/50 text-[#00c389] flex items-center justify-center font-bold">
                        <i data-lucide="keyboard" class="w-4 h-4"></i>
                    </div>
                    <h3 class="font-bold text-base text-slate-900 dark:text-white font-display">System Keyboard Hotkeys</h3>
                </div>
                <button @click="hotkeysModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="space-y-2.5 text-xs">
                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-[#142034]">
                    <span class="text-slate-700 dark:text-slate-300">Universal Command Palette</span>
                    <kbd class="px-2 py-1 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[11px] font-bold shadow-2xs">Ctrl + K / ⌘K</kbd>
                </div>
                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-[#142034]">
                    <span class="text-slate-700 dark:text-slate-300">Open QueueBot AI Assistant</span>
                    <kbd class="px-2 py-1 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[11px] font-bold shadow-2xs">Ctrl + J / ⌘J</kbd>
                </div>
                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-[#142034]">
                    <span class="text-slate-700 dark:text-slate-300">Call Next Waiting Student</span>
                    <kbd class="px-2 py-1 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[11px] font-bold shadow-2xs">Space</kbd>
                </div>
                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-[#142034]">
                    <span class="text-slate-700 dark:text-slate-300">Start Active Consultation</span>
                    <kbd class="px-2 py-1 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[11px] font-bold shadow-2xs">Alt + S</kbd>
                </div>
                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-[#142034]">
                    <span class="text-slate-700 dark:text-slate-300">Complete & Clear Desk</span>
                    <kbd class="px-2 py-1 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[11px] font-bold shadow-2xs">Alt + D</kbd>
                </div>
                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-[#142034]">
                    <span class="text-slate-700 dark:text-slate-300">Recall Active Token</span>
                    <kbd class="px-2 py-1 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[11px] font-bold shadow-2xs">Alt + R</kbd>
                </div>
                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-[#142034]">
                    <span class="text-slate-700 dark:text-slate-300">Toggle Hotkey Reference</span>
                    <kbd class="px-2 py-1 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[11px] font-bold shadow-2xs">?</kbd>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 text-center font-mono pt-1">Press <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold">Esc</kbd> anytime to dismiss</p>
        </div>
    </div>
                    <span class="text-slate-700">Toggle Hotkey Reference</span>
                    <kbd class="px-2 py-1 rounded bg-white border border-slate-200 font-mono text-[11px] font-bold shadow-2xs">?</kbd>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 text-center font-mono pt-1">Press <kbd class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-bold">Esc</kbd> anytime to dismiss</p>
        </div>
    </div>

    <!-- Master Floating Rounded Canvas (Inspired by HOMEAGE Reference Image) -->
    <div class="max-w-7xl mx-auto w-full bg-white dark:bg-[#0c1322] rounded-[2.5rem] shadow-canvas border border-white/60 dark:border-slate-800/80 overflow-hidden flex flex-col transition-all duration-300">

        <!-- Top Navigation Header inside Canvas (Matching Reference) -->
        <header class="no-print bg-white/95 dark:bg-[#0c1322]/95 backdrop-blur-md border-b border-slate-100 dark:border-slate-800/80 px-6 sm:px-8 py-5 transition-all">
            <div class="flex items-center justify-between gap-4">
                
                <!-- Left: Brand Logo Pill with dynamic light/dark logo -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" @click="window.playTactileSound('click')" class="flex items-center group transition-all duration-300 hover:scale-105 active:scale-95" title="QueueLess Dashboard">
                        <img src="{{ asset('images/logo-light.png') }}" alt="QueueLess" class="h-9 sm:h-10 w-auto object-contain drop-shadow-2xs block dark:hidden" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
                        <img src="{{ asset('images/logo-transparent.png') }}" alt="QueueLess" class="h-9 sm:h-10 w-auto object-contain drop-shadow-[0_2px_15px_rgba(0,195,137,0.35)] hidden dark:block" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
                    </a>
                </div>

                <!-- Center: Segmented Navigation Capsule Pills (Matching Reference Tabs) -->
                <nav class="hidden md:flex items-center gap-1.5 p-1 rounded-full bg-slate-100/80 dark:bg-[#121c2e] border border-slate-200/60 dark:border-slate-800 text-xs font-semibold">
                    <a href="{{ route('home') }}" @click="window.playTactileSound('click')"
                       class="px-5 py-2 rounded-full transition-all duration-300 {{ request()->routeIs('home') ? 'bg-[#00c389] text-white font-bold shadow-sm shadow-mint' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        Dashboard
                    </a>
                    @auth
                        @if(auth()->user()->isStudent() || auth()->user()->isAdmin())
                            <a href="{{ route('student.dashboard') }}" @click="window.playTactileSound('click')"
                               class="px-5 py-2 rounded-full transition-all duration-300 {{ request()->routeIs('student.dashboard') ? 'bg-[#00c389] text-white font-bold shadow-sm shadow-mint' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                My Ticket
                            </a>
                            <a href="{{ route('student.join') }}" @click="window.playTactileSound('click')"
                               class="px-5 py-2 rounded-full transition-all duration-300 {{ request()->routeIs('student.join') ? 'bg-[#00c389] text-white font-bold shadow-sm shadow-mint' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                Take Token
                            </a>
                            <a href="{{ route('student.history') }}" @click="window.playTactileSound('click')"
                               class="px-5 py-2 rounded-full transition-all duration-300 {{ request()->routeIs('student.history') ? 'bg-[#00c389] text-white font-bold shadow-sm shadow-mint' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                History
                            </a>
                        @endif

                        @if(auth()->user()->isOperator() || auth()->user()->isAdmin())
                            <a href="{{ route('operator.dashboard') }}" @click="window.playTactileSound('click')"
                               class="px-5 py-2 rounded-full transition-all duration-300 {{ request()->routeIs('operator.dashboard') ? 'bg-[#00c389] text-white font-bold shadow-sm shadow-mint' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                Operator Overview
                            </a>
                            <a href="{{ route('operator.console') }}" @click="window.playTactileSound('click')"
                               class="px-5 py-2 rounded-full transition-all duration-300 {{ request()->routeIs('operator.console') ? 'bg-[#00c389] text-white font-bold shadow-sm shadow-mint' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                Operator Desk
                            </a>
                        @endif

                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" @click="window.playTactileSound('click')"
                               class="px-5 py-2 rounded-full transition-all duration-300 {{ request()->routeIs('admin.dashboard') ? 'bg-[#00c389] text-white font-bold shadow-sm shadow-mint' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                Analytics
                            </a>
                        @endif
                    @endauth
                    @guest
                        <a href="{{ route('operator.login') }}" @click="window.playTactileSound('click')"
                           class="px-5 py-2 rounded-full transition-all duration-300 {{ request()->routeIs('operator.login', 'operator.register', 'operator.clearance.pending') ? 'bg-[#00c389] text-white font-bold shadow-sm shadow-mint' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                            Operator Desk
                        </a>
                    @endguest
                    <a href="{{ route('public.display') }}" target="_blank"
                       class="px-4 py-2 rounded-full text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition flex items-center gap-1">
                        Signage TV <i data-lucide="external-link" class="w-3 h-3 opacity-60"></i>
                    </a>
                </nav>

                <!-- Right: Action Circles, Theme Toggle & User Profile -->
                <div class="flex items-center gap-2 sm:gap-2.5">
                    <!-- Modern Theme Toggle Button in Header -->
                    <button @click="toggleTheme()"
                            class="w-9 h-9 rounded-full bg-slate-50 dark:bg-[#142034] hover:bg-slate-100 dark:hover:bg-[#1b2b46] border border-slate-200/80 dark:border-slate-800 text-slate-700 dark:text-amber-300 flex items-center justify-center transition-all duration-300 shadow-xs hover:scale-105 active:scale-95"
                            :title="theme === 'dark' ? 'Switch to Radiant Bright Light Mode' : 'Switch to Obsidian Dark Mode'">
                        <i x-show="theme === 'dark'" data-lucide="sun" class="w-4 h-4 text-amber-400"></i>
                        <i x-show="theme === 'light'" data-lucide="moon" class="w-4 h-4 text-slate-700"></i>
                    </button>

                    <!-- Search Command Palette Button -->
                    <button @click="commandPaletteOpen = true; $nextTick(() => { document.getElementById('paletteSearchInput')?.focus(); })"
                            class="w-9 h-9 rounded-full bg-slate-50 dark:bg-[#142034] hover:bg-slate-100 dark:hover:bg-[#1b2b46] border border-slate-200/80 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition shadow-xs hover:scale-105 active:scale-95"
                            title="Command Palette (Ctrl+K)">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </button>

                    <!-- TV Broadcast Quick Button -->
                    <a href="{{ route('public.display') }}" target="_blank"
                       class="w-9 h-9 rounded-full bg-slate-50 dark:bg-[#142034] hover:bg-slate-100 dark:hover:bg-[#1b2b46] border border-slate-200/80 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition shadow-xs hover:scale-105 active:scale-95"
                       title="4K TV Broadcast">
                        <i data-lucide="tv" class="w-4 h-4"></i>
                    </a>

                    <!-- Notification Bell with Ping Dot -->
                    <div class="relative">
                        <button @click="addToast('All campus desks operational and synchronized', 'info')" class="w-9 h-9 rounded-full bg-slate-50 dark:bg-[#142034] hover:bg-slate-100 dark:hover:bg-[#1b2b46] border border-slate-200/80 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition shadow-xs hover:scale-105 active:scale-95">
                            <i data-lucide="bell" class="w-4 h-4"></i>
                        </button>
                        <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-[#00c389] animate-pulse"></span>
                    </div>

                    <div class="h-6 w-px bg-slate-200 dark:bg-slate-800 mx-1 hidden sm:block"></div>

                    <!-- User Profile Chip -->
                    @auth
                        <div class="flex items-center gap-2.5 pl-1">
                            <div class="w-9 h-9 rounded-full bg-slate-100 dark:bg-[#172338] border border-slate-200 dark:border-slate-700 overflow-hidden flex items-center justify-center text-slate-700 dark:text-slate-200 font-bold text-xs shrink-0 shadow-xs">
                                <span>{{ substr(auth()->user()->name, 0, 1) }}</span>
                            </div>
                            <div class="hidden lg:block text-left">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-100 block leading-tight">{{ auth()->user()->name }}</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium block">{{ auth()->user()->role === 'COUNTER_OPERATOR' ? 'Counter Officer' : (auth()->user()->isAdmin() ? 'Administrator' : 'Student') }}</span>
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="ml-1">
                                @csrf
                                <button type="submit" class="w-8 h-8 rounded-full bg-slate-50 dark:bg-[#142034] hover:bg-rose-50 dark:hover:bg-rose-950/40 hover:text-rose-600 text-slate-400 flex items-center justify-center transition" title="Sign Out">
                                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="flex items-center gap-2">
                            @php
                                $selectedRole = session('selected_role', 'student');
                                $roleAwareOperator = request()->is('operator/*') || $selectedRole === 'operator';
                                $roleAwareLoginRoute = match ($selectedRole) {
                                    'operator' => 'operator.login',
                                    'admin' => 'admin.login',
                                    default => 'student.login',
                                };
                                $roleAwareRegisterRoute = $roleAwareOperator ? 'operator.register' : 'register';
                            @endphp
                            <a href="{{ route($roleAwareLoginRoute) }}" class="px-4 py-2 rounded-full text-xs font-bold text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white transition">
                                Sign In
                            </a>
                            <a href="{{ route($roleAwareRegisterRoute) }}" class="px-4 py-2 rounded-full text-xs font-bold {{ $roleAwareOperator ? 'bg-amber-500 hover:bg-amber-600' : 'bg-[#00c389] hover:bg-emerald-600 shadow-mint' }} text-white shadow-sm transition">
                                Register
                            </a>
                        </div>
                    @endauth

                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenu = !mobileMenu" class="md:hidden w-9 h-9 rounded-full bg-slate-50 dark:bg-[#142034] border border-slate-200/80 dark:border-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300">
                        <i data-lucide="menu" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Drawer Dropdown -->
            <div x-show="mobileMenu" x-cloak class="md:hidden pt-4 pb-2 border-t border-slate-100 dark:border-slate-800 mt-4 space-y-2">
                <a href="{{ route('home') }}" class="block px-4 py-2 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-[#142034]">Dashboard</a>
                @auth
                    <a href="{{ route('student.dashboard') }}" class="block px-4 py-2 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-[#142034]">My Ticket</a>
                    <a href="{{ route('student.join') }}" class="block px-4 py-2 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-[#142034]">Take Token</a>
                    <a href="{{ route('student.history') }}" class="block px-4 py-2 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-[#142034]">History</a>
                    @if(auth()->user()->isOperator() || auth()->user()->isAdmin())
                        <a href="{{ route('operator.dashboard') }}" class="block px-4 py-2 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-[#142034]">Operator Overview</a>
                        <a href="{{ route('operator.console') }}" class="block px-4 py-2 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-[#142034]">Operator Desk</a>
                    @endif
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-[#142034]">Admin Analytics</a>
                    @endif
                @endauth
                @guest
                    <a href="{{ route('operator.login') }}" class="block px-4 py-2 rounded-xl text-xs font-bold {{ request()->routeIs('operator.login', 'operator.register', 'operator.clearance.pending') ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'text-slate-800 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-[#142034]' }}">Operator Desk</a>
                @endguest
                <a href="{{ route('public.display') }}" target="_blank" class="block px-4 py-2 rounded-xl text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40">Signage TV</a>
            </div>
        </header>

        <!-- Main Workspace Body -->
        <main class="flex-1 bg-slate-50/70 dark:bg-[#080d17] p-5 sm:p-7 lg:p-8 transition-colors duration-300">
            @yield('content')
        </main>

        <!-- Clean Canvas Footer -->
        <footer class="no-print bg-white dark:bg-[#0c1322] border-t border-slate-100 dark:border-slate-800/80 px-6 sm:px-8 py-5 text-xs text-slate-400 dark:text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-3 transition-colors duration-300">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('images/logo-icon.png') }}" alt="QueueLess" class="w-6 h-6 rounded-lg object-contain shadow-xs">
                <span class="font-bold text-slate-700 dark:text-slate-200">QueueLess Campus System</span>
                <span class="text-slate-400 dark:text-slate-500">• Real-Time University Queue Operations</span>
            </div>
            <div class="flex items-center gap-4 text-[11px] font-mono text-slate-400 dark:text-slate-500">
                <span>Erlang-C Dynamic Capacity</span>
                <span>•</span>
                <span>Pessimistic Concurrency</span>
                <span>•</span>
                <span>Web Audio Synthesizer</span>
            </div>
        </footer>

    </div>

    <!-- ======================================================== -->
    <!-- FLOATING AI CAMPUS ASSISTANT TRIGGER & DRAWER (QUEUEBOT) -->
    <!-- ======================================================== -->
    <div x-data="aiCampusBot()" x-init="init()" class="no-print">
        
        <!-- Floating Pill / Avatar Button (Bottom-Right) -->
        <div class="fixed bottom-6 right-6 z-40 flex items-center gap-2">
            <button @click="toggleDrawer()"
                    class="relative group flex items-center gap-2.5 px-4 py-3 rounded-full bg-slate-900 dark:bg-[#121c2e] text-white shadow-2xl hover:bg-slate-800 dark:hover:bg-[#172338] hover:scale-105 active:scale-95 transition-all duration-300 border border-slate-700 dark:border-slate-700">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#00c389] opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-[#00c389]"></span>
                </span>
                <div class="flex items-center gap-1.5 font-bold text-xs">
                    <i data-lucide="bot" class="w-4 h-4 text-[#00c389]"></i>
                    <span>QueueBot AI</span>
                </div>
                <span class="hidden sm:inline px-1.5 py-0.5 rounded bg-white/10 text-[9px] font-mono text-slate-300">⌘J</span>
            </button>
        </div>

        <!-- Sliding Chat Drawer (Bottom-Right Floating Box) -->
        <div x-show="drawerOpen" x-cloak
             @click.away="drawerOpen = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-6 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-6 scale-95"
             class="fixed bottom-22 right-6 z-50 w-96 max-w-[calc(100vw-3rem)] bg-white dark:bg-[#0c1322] rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col h-[520px]">
            
            <!-- Chat Header -->
            <div class="p-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-2xl bg-[#00c389]/20 border border-[#00c389]/40 flex items-center justify-center text-[#00c389]">
                        <i data-lucide="bot" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-bold text-sm">QueueBot AI</span>
                            <span class="w-2 h-2 rounded-full bg-[#00c389] animate-pulse"></span>
                        </div>
                        <span class="text-[10px] text-slate-400 block font-mono">BAUST Campus Intelligence</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button @click="voiceEnabled = !voiceEnabled; if (window.playTactileSound) window.playTactileSound('click')"
                            :class="voiceEnabled ? 'text-[#00c389]' : 'text-slate-400'"
                            class="p-1.5 rounded-lg hover:bg-white/10 transition" title="Toggle Voice Response">
                        <i :data-lucide="voiceEnabled ? 'volume-2' : 'volume-x'" class="w-4 h-4"></i>
                    </button>
                    <button @click="drawerOpen = false" class="p-1.5 rounded-lg hover:bg-white/10 text-slate-400 hover:text-white transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Chat Messages Container -->
            <div id="aiChatMessages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-50/60 dark:bg-[#080d17] text-xs">
                <template x-for="(msg, idx) in messages" :key="idx">
                    <div class="flex flex-col" :class="msg.sender === 'user' ? 'items-end' : 'items-start'">
                        <div class="max-w-[85%] rounded-2xl p-3 shadow-xs"
                             :class="msg.sender === 'user' ? 'bg-[#00c389] text-white rounded-br-xs font-medium' : 'bg-white dark:bg-[#142034] text-slate-800 dark:text-slate-100 border border-slate-200/80 dark:border-slate-700 rounded-bl-xs'">
                            <p class="leading-relaxed whitespace-pre-line" x-html="msg.text"></p>
                            
                            <!-- Action button if present in message -->
                            <template x-if="msg.action">
                                <div class="mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                                    <a :href="msg.action.url" @click="if (window.playTactileSound) window.playTactileSound('click')"
                                       class="px-3 py-1 rounded-full bg-[#00c389] text-white font-bold text-[10px] flex items-center gap-1 shadow-2xs hover:bg-emerald-600 transition">
                                        <span x-text="msg.action.label"></span>
                                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                    </a>
                                </div>
                            </template>
                        </div>
                        <span class="text-[9px] text-slate-400 font-mono mt-1 px-1" x-text="msg.time"></span>
                    </div>
                </template>

                <div x-show="isTyping" class="flex items-center gap-1.5 p-3 max-w-[50%] bg-white dark:bg-[#142034] rounded-2xl border border-slate-200 dark:border-slate-700 text-slate-400 text-xs">
                    <span class="w-2 h-2 rounded-full bg-[#00c389] animate-bounce"></span>
                    <span class="w-2 h-2 rounded-full bg-[#00c389] animate-bounce" style="animation-delay: 0.15s"></span>
                    <span class="w-2 h-2 rounded-full bg-[#00c389] animate-bounce" style="animation-delay: 0.3s"></span>
                </div>
            </div>

            <!-- Quick Suggestion Action Chips -->
            <div class="p-2 bg-white dark:bg-[#0c1322] border-t border-slate-100 dark:border-slate-800 flex items-center gap-1.5 overflow-x-auto text-[10px] font-semibold text-slate-600 dark:text-slate-300">
                <button @click="askQuestion('Which desk has the shortest line right now?')" class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-[#142034] hover:bg-mint-50 dark:hover:bg-mint-950/40 hover:text-mint-800 dark:hover:text-mint-300 shrink-0 transition">
                    ⚡ Shortest Queue
                </button>
                <button @click="askQuestion('What documents are required for Transcript Attestation?')" class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-[#142034] hover:bg-mint-50 dark:hover:bg-mint-950/40 hover:text-mint-800 dark:hover:text-mint-300 shrink-0 transition">
                    📄 Attestation Docs
                </button>
                <button @click="askQuestion('When is the best time to visit Accounts for fees?')" class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-[#142034] hover:bg-mint-50 dark:hover:bg-mint-950/40 hover:text-mint-800 dark:hover:text-mint-300 shrink-0 transition">
                    🕒 Peak Fee Hours
                </button>
                <button @click="askQuestion('How does Erlang-C estimate my wait time?')" class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-[#142034] hover:bg-mint-50 dark:hover:bg-mint-950/40 hover:text-mint-800 dark:hover:text-mint-300 shrink-0 transition">
                    🧮 Erlang-C Math
                </button>
            </div>

            <!-- Input Bar -->
            <form @submit.prevent="sendMessage()" class="p-3 bg-white dark:bg-[#0c1322] border-t border-slate-100 dark:border-slate-800 flex items-center gap-2">
                <input type="text"
                       x-model="inputQuery"
                       placeholder="Ask about queues, offices, requirements..."
                       class="flex-1 bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 rounded-full px-4 py-2 text-xs text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-[#00c389] focus:bg-white dark:focus:bg-[#172338] transition">
                <button type="submit"
                        :disabled="!inputQuery.trim() || isTyping"
                        class="w-8 h-8 rounded-full bg-[#00c389] hover:bg-emerald-600 disabled:opacity-40 text-white flex items-center justify-center transition shadow-xs">
                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                </button>
            </form>
        </div>

    </div>

    <!-- QueueBot AI Logic & Campus Knowledge Engine -->
    <script>
    function aiCampusBot() {
        return {
            drawerOpen: false,
            voiceEnabled: false,
            isTyping: false,
            inputQuery: '',
            messages: [
                {
                    sender: 'bot',
                    text: '👋 Hello! I am **QueueBot**, your intelligent BAUST Campus Queue Oracle.\n\nAsk me about current queue delays, required clearance documents, desk locations, or best arrival times!',
                    time: 'Just now'
                }
            ],

            init() {
                window.addEventListener('openAiDrawer', () => { this.drawerOpen = true; });
                this.$watch('drawerOpen', val => {
                    if (val) {
                        this.$nextTick(() => {
                            lucide.createIcons();
                            this.scrollToBottom();
                        });
                    }
                });
            },

            toggleDrawer() {
                this.drawerOpen = !this.drawerOpen;
                if (window.playTactileSound) window.playTactileSound('click');
            },

            scrollToBottom() {
                const el = document.getElementById('aiChatMessages');
                if (el) el.scrollTop = el.scrollHeight;
            },

            askQuestion(q) {
                this.inputQuery = q;
                this.sendMessage();
            },

            sendMessage() {
                const q = this.inputQuery.trim();
                if (!q) return;

                const timeStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                this.messages.push({ sender: 'user', text: q, time: timeStr });
                this.inputQuery = '';
                this.isTyping = true;
                if (window.playTactileSound) window.playTactileSound('click');
                this.$nextTick(() => this.scrollToBottom());

                setTimeout(() => {
                    const response = this.generateResponse(q);
                    this.messages.push({
                        sender: 'bot',
                        text: response.text,
                        action: response.action,
                        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                    });
                    this.isTyping = false;
                    if (window.playTactileSound) window.playTactileSound('chime');

                    // Voice announcement if enabled
                    if (this.voiceEnabled && ('speechSynthesis' in window)) {
                        const cleanText = response.text.replace(/[*_#`]/g, '');
                        const utter = new SpeechSynthesisUtterance(cleanText);
                        utter.rate = 1.05;
                        window.speechSynthesis.speak(utter);
                    }

                    this.$nextTick(() => {
                        lucide.createIcons();
                        this.scrollToBottom();
                    });
                }, 450);
            },

            generateResponse(query) {
                const q = query.toLowerCase();

                if (q.includes('shortest') || q.includes('fastest') || q.includes('least wait')) {
                    return {
                        text: "🟢 **Shortest Queue Right Now:**\n\n• **Medical Center (MED)**: 1 student in line (~2m wait)\n• **CSE Dept Office (CSE)**: 2 students in line (~3m wait)\n\nBoth offices have active counters with immediate availability.",
                        action: { label: 'Get Fast Pass for CSE', url: '/student/join?dept_id=5' }
                    };
                }

                if (q.includes('document') || q.includes('attestation') || q.includes('transcript')) {
                    return {
                        text: "📄 **Transcript Attestation Requirements (Registrar Office):**\n\n1. Original Academic Grade Sheet / Certificate\n2. 2 Photocopies for official stamping\n3. Valid Campus Card (Original)\n4. Accounts Fee Clearance Challan (৳300)",
                        action: { label: 'Take Registrar Token', url: '/student/join?dept_id=1' }
                    };
                }

                if (q.includes('fee') || q.includes('accounts') || q.includes('tuition') || q.includes('peak')) {
                    return {
                        text: "🕒 **Accounts & Finance Peak Hours Advisory:**\n\n• **Peak Congestion**: 10:30 AM – 12:30 PM (Average 18 min wait)\n• **Recommended Optimal Time**: 08:30 AM – 09:30 AM or 02:00 PM – 03:00 PM (Sub-4 min wait)\n\nAccounts Counter 1 & 2 accept Bank Challan and online mobile money receipts.",
                        action: { label: 'Join Accounts Queue', url: '/student/join?dept_id=2' }
                    };
                }

                if (q.includes('erlang') || q.includes('math') || q.includes('formula') || q.includes('calculate')) {
                    return {
                        text: "🧮 **QueueLess M/M/c Erlang-C Predictive Engine:**\n\nInstead of naive averages, our server continuously computes:\n\n• Arrival rate: $\\lambda$ (Poisson)\n• Service rate: $\\mu$ per desk\n• Erlang-C delay probability: $C(c, a)$\n• Expected wait time: $W_q = \\frac{C(c, a)}{c\\mu - \\lambda}$\n\nIf utilization $\\rho \\ge 0.85$, administrators are automatically alerted to provision extra desks!",
                        action: { label: 'View Admin Analytics', url: '/admin/dashboard' }
                    };
                }

                if (q.includes('ticket') || q.includes('token') || q.includes('pass') || q.includes('dispense')) {
                    return {
                        text: "🎟️ You can claim an instantaneous digital boarding pass right now without downloading any mobile app! Select your destination office below:",
                        action: { label: 'Dispense Digital Pass', url: '/student/join' }
                    };
                }

                return {
                    text: "🤖 I am trained on the **BAUST Campus Queue Operations Matrix**.\n\nYou can ask me:\n• *'What documents are needed for Admit Card clearance?'*\n• *'Where is the Provost Hall Office located?'*\n• *'Which desk has the shortest line?'*\n• *'What is my expected wait time?'*",
                    action: { label: 'Browse All Campus Desks', url: '/#desks' }
                };
            }
        };
    }
    </script>

    @stack('scripts')
</body>
</html>
