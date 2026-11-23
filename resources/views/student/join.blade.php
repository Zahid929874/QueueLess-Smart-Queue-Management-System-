@extends('layouts.app', ['title' => 'Join Campus Queue'])

@section('content')
<div x-data="joinQueueForm()" x-init="init()" class="max-w-2xl mx-auto space-y-6">

    <!-- Header & Step Wizard Indicator -->
    <div class="text-center space-y-3">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-mint-50 dark:bg-mint-950/50 border border-mint-200 dark:border-mint-800 text-[#00c389] text-xs font-bold font-mono">
            <span class="w-1.5 h-1.5 rounded-full bg-[#00c389] animate-pulse"></span>
            <span>SMART TOKEN DISPENSER</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight font-display">Dispense Digital Queue Pass</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
            Select your campus office and service clearance to generate an instant digital boarding pass with live countdown.
        </p>

        <!-- 3-Step Connected Wizard Indicator -->
        <div class="max-w-md mx-auto pt-2 flex items-center justify-between text-[11px] font-mono">
            <div class="flex items-center gap-2" :class="selectedDeptId ? 'text-[#00c389] font-bold' : 'text-slate-400 dark:text-slate-500'">
                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-black"
                      :class="selectedDeptId ? 'bg-[#00c389] text-white shadow-2xs' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400'">1</span>
                <span>Office</span>
            </div>
            <div class="flex-1 h-0.5 mx-2" :class="selectedDeptId ? 'bg-[#00c389]' : 'bg-slate-200 dark:bg-slate-800'"></div>
            <div class="flex items-center gap-2" :class="selectedServiceId ? 'text-[#00c389] font-bold' : 'text-slate-400 dark:text-slate-500'">
                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-black"
                      :class="selectedServiceId ? 'bg-[#00c389] text-white shadow-2xs' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400'">2</span>
                <span>Service</span>
            </div>
            <div class="flex-1 h-0.5 mx-2" :class="selectedServiceId ? 'bg-[#00c389]' : 'bg-slate-200 dark:bg-slate-800'"></div>
            <div class="flex items-center gap-2" :class="submitting ? 'text-[#00c389] font-bold' : 'text-slate-400 dark:text-slate-500'">
                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-black"
                      :class="submitting ? 'bg-[#00c389] text-white animate-spin' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400'">3</span>
                <span>Pass</span>
            </div>
        </div>
    </div>

    <!-- Quick Search Input -->
    <div class="relative">
        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
        <input type="text"
               x-model="serviceFilter"
               @input="filterServices()"
               placeholder="Quick search services (e.g. transcript, dues, doctor, wifi, grade sheet)..."
               class="w-full pl-11 pr-10 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 bg-white dark:bg-[#142034] focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] shadow-xs transition">
        <button x-show="serviceFilter" @click="serviceFilter = ''; filterServices()" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
            <i data-lucide="x" class="w-3.5 h-3.5"></i>
        </button>
    </div>

    <!-- Main Dispenser Form Card (White Rounded Canvas Style) -->
    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-6 sm:p-8 shadow-card border border-slate-100 dark:border-slate-800 interactive-card space-y-6 transition-colors duration-300">
        <form @submit.prevent="submitJoin()" class="space-y-6">

            <!-- Step 1: Select Department -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <label class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-[#00c389] text-white flex items-center justify-center text-[10px] font-black">1</span>
                        Choose Service Department
                    </label>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 font-mono">6 Campus Offices Active</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($departments as $dept)
                        @php
                            $waiting = $dept->queueTickets()->where('status', 'WAITING')->count();
                        @endphp
                        <label class="relative flex flex-col justify-between p-4 rounded-xl border cursor-pointer transition text-left group hover:scale-[1.01]"
                               :class="selectedDeptId == {{ $dept->id }} ? 'border-[#00c389] bg-mint-50/60 dark:bg-mint-950/30 ring-2 ring-[#00c389] shadow-xs' : 'border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-[#121c2e]/60 hover:bg-white dark:hover:bg-[#162238] hover:border-slate-300 dark:hover:border-slate-700'">
                            <input type="radio" name="department_id" value="{{ $dept->id }}" x-model="selectedDeptId" @change="onDeptChange()" class="sr-only">

                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-black font-mono px-2 py-0.5 rounded bg-white dark:bg-[#18253d] text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700">{{ $dept->code }}</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $waiting <= 2 ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60' }}">
                                        {{ $waiting }} in line
                                    </span>
                                </div>
                                <h3 class="font-bold text-xs text-slate-900 dark:text-white mt-2 block group-hover:text-[#00c389] transition">{{ $dept->name }}</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $dept->location ?? 'Campus Office' }}</p>
                            </div>

                            <div class="pt-2 mt-2 border-t border-slate-200/60 dark:border-slate-800 flex items-center justify-between text-[10px] text-slate-400 dark:text-slate-500 font-mono">
                                <span>Avg: ~{{ $dept->avg_handling_time_min }}m</span>
                                <span class="font-sans font-bold text-[#00c389] group-hover:translate-x-0.5 transition">Select &rarr;</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Step 2: Select Service -->
            <div x-show="selectedDeptId" x-transition>
                <div class="flex items-center justify-between mb-3">
                    <label class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-[#00c389] text-white flex items-center justify-center text-[10px] font-black">2</span>
                        Select Required Clearance
                    </label>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 font-mono" x-text="availableServices.length + ' Available Services'"></span>
                </div>

                <div class="space-y-2">
                    <template x-for="service in availableServices" :key="service.id">
                        <label class="flex items-center justify-between p-3.5 rounded-xl border cursor-pointer transition hover:scale-[1.01]"
                               :class="selectedServiceId == service.id ? 'border-[#00c389] bg-mint-50/60 dark:bg-mint-950/30 ring-2 ring-[#00c389] shadow-xs' : 'border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-[#121c2e]/60 hover:bg-white dark:hover:bg-[#162238]'">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="service_id" :value="service.id" x-model="selectedServiceId" class="text-[#00c389] focus:ring-[#00c389]">
                                <div>
                                    <span class="font-bold text-xs text-slate-900 dark:text-white block" x-text="service.name"></span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5" x-text="service.description || 'Standard university service request'"></span>
                                </div>
                            </div>
                            <span class="text-[11px] font-mono font-bold text-[#00c389] px-2.5 py-1 rounded-lg bg-mint-50 dark:bg-mint-950/50 border border-mint-200 dark:border-mint-800 shrink-0" x-text="'~' + service.estimated_duration_min + ' min'"></span>
                        </label>
                    </template>
                </div>
            </div>

            <!-- Step 3: Optional Purpose Note -->
            <div x-show="selectedServiceId" x-transition class="space-y-2">
                <label for="notes" class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center text-[10px] font-black">3</span>
                    Optional Purpose Notes
                </label>
                <textarea id="notes" x-model="notes" rows="2"
                          placeholder="e.g. Urgent transcript, dues clearance receipt slip"
                          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#142034] text-xs text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389]"></textarea>
            </div>

            <!-- Live Pass Preview Before Confirmation -->
            <div x-show="selectedServiceObj" x-transition class="p-5 rounded-2xl bg-slate-900 text-white space-y-3 border border-slate-800 shadow-xl relative overflow-hidden">
                <div class="flex items-center justify-between text-[10px] font-mono text-slate-400 border-b border-slate-800 pb-2">
                    <span class="flex items-center gap-1.5 text-mint-accent">
                        <span class="w-2 h-2 rounded-full bg-mint-accent animate-pulse"></span>
                        PASS PREVIEW
                    </span>
                    <span>BAUST CAMPUS DISPATCH</span>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-white/10 text-mint-accent border border-white/10" x-text="selectedDeptObj ? selectedDeptObj.code : ''"></span>
                        <h4 class="font-bold text-sm text-white mt-1" x-text="selectedDeptObj ? selectedDeptObj.name : ''"></h4>
                        <span class="text-xs text-slate-400 block" x-text="selectedServiceObj ? selectedServiceObj.name : ''"></span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] uppercase font-mono text-slate-400 block">Est. Wait</span>
                        <span class="text-xl font-black font-mono text-mint-accent" x-text="'~' + (selectedServiceObj ? selectedServiceObj.estimated_duration_min : 5) + ' min'"></span>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-[10px] font-mono text-slate-400">
                    <span>Location: <strong class="text-slate-200" x-text="selectedDeptObj ? (selectedDeptObj.location || 'Campus Desk') : ''"></strong></span>
                    <span class="text-amber-400">⚡ Instant Digital Dispatch</span>
                </div>
            </div>

            <!-- Error Banner -->
            <div x-show="errorMessage" x-cloak class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-rose-600"></i>
                <span x-text="errorMessage"></span>
            </div>

            <!-- Submit Button (Pill with Ripple Feedback) -->
            <button type="submit"
                    :disabled="!selectedServiceId || submitting"
                    class="w-full py-3.5 rounded-full bg-[#00c389] hover:bg-emerald-600 disabled:opacity-50 text-white font-bold text-sm shadow-md shadow-mint active:scale-95 transition-all flex items-center justify-center gap-2">
                <template x-if="submitting">
                    <span class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                </template>
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span x-text="submitting ? 'Printing Digital Token Slip...' : 'Confirm & Dispense Token Slip'"></span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function joinQueueForm() {
    return {
        departments: @json($departments),
        selectedDeptId: '{{ $selectedDeptId ?? ($departments->first()->id ?? '') }}',
        selectedServiceId: null,
        availableServices: [],
        serviceFilter: '',
        notes: '',
        submitting: false,
        errorMessage: null,

        get selectedDeptObj() {
            return this.departments.find(d => d.id == this.selectedDeptId);
        },

        get selectedServiceObj() {
            return this.availableServices.find(s => s.id == this.selectedServiceId);
        },

        init() {
            this.onDeptChange();
            lucide.createIcons();
        },

        onDeptChange() {
            if (window.playTactileSound) window.playTactileSound('click');
            const dept = this.departments.find(d => d.id == this.selectedDeptId);
            if (dept && dept.services) {
                this.availableServices = dept.services;
                this.selectedServiceId = dept.services.length > 0 ? dept.services[0].id : null;
            } else {
                this.availableServices = [];
                this.selectedServiceId = null;
            }
            this.errorMessage = null;
            this.$nextTick(() => lucide.createIcons());
        },

        filterServices() {
            if (!this.serviceFilter) {
                this.onDeptChange();
                return;
            }
            const q = this.serviceFilter.toLowerCase();
            for (const dept of this.departments) {
                const match = dept.services.find(s => s.name.toLowerCase().includes(q) || (s.description && s.description.toLowerCase().includes(q)));
                if (match) {
                    this.selectedDeptId = dept.id;
                    this.availableServices = dept.services;
                    this.selectedServiceId = match.id;
                    return;
                }
            }
        },

        async submitJoin() {
            if (!this.selectedServiceId) return;
            this.submitting = true;
            this.errorMessage = null;
            if (window.playTactileSound) window.playTactileSound('dispense');

            try {
                const res = await fetch('/api/queues/join', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        service_id: this.selectedServiceId,
                        notes: this.notes
                    })
                });

                const data = await res.json();
                if (data.success) {
                    if (window.playTactileSound) window.playTactileSound('chime');
                    if (window.showToast) window.showToast("Queue token issued successfully!", "success");
                    window.location.href = "{{ route('student.dashboard') }}";
                } else {
                    this.errorMessage = data.message || "Failed to join queue.";
                    this.submitting = false;
                }
            } catch (err) {
                this.errorMessage = "Network or server connection error.";
                this.submitting = false;
            }
        }
    }
}
</script>
@endpush
@endsection
