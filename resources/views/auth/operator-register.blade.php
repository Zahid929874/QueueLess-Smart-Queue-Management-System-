@extends('layouts.app', ['title' => 'Operator Access Request'])

@section('content')
<div x-data="operatorRegistration()" class="max-w-lg mx-auto py-6 sm:py-8">
    <div class="bg-white dark:bg-[#0f172a] rounded-3xl p-8 sm:p-9 border border-slate-100 dark:border-slate-800 shadow-card space-y-6 interactive-card">
        <div class="text-center space-y-3">
            <a href="{{ route('home') }}" class="inline-block transition-transform hover:scale-105 active:scale-95" title="QueueLess">
                <img src="{{ asset('images/logo-light.png') }}" alt="QueueLess" class="dark:hidden h-12 sm:h-14 w-auto mx-auto object-contain drop-shadow-xs" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
                <img src="{{ asset('images/logo-transparent.png') }}" alt="QueueLess" class="hidden dark:block h-12 sm:h-14 w-auto mx-auto object-contain drop-shadow-xs" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
            </a>
            <p class="text-xs text-slate-500 dark:text-slate-400">Request access to run a service counter, call tokens and serve students.</p>
        </div>

        <form method="POST" action="{{ route('operator.register.post') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="officer_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Officer Name</label>
                    <input id="officer_name" name="officer_name" type="text" value="{{ old('officer_name') }}" placeholder="As on your staff ID card" required
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] placeholder-slate-400 transition">
                    @error('officer_name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="badge_no" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Staff Badge No.</label>
                    <input id="badge_no" name="badge_no" type="text" value="{{ old('badge_no') }}" placeholder="STF-0042" required
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm font-mono focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] placeholder-slate-400 transition">
                    @error('badge_no') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="official_mail" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Official Mail</label>
                <input id="official_mail" name="official_mail" type="email" value="{{ old('official_mail') }}" placeholder="name@queueless.local" required
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] placeholder-slate-400 transition">
                @error('official_mail') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="service_unit_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Service Unit</label>
                    <select id="service_unit_id" name="service_unit_id" x-model="serviceUnitId" @change="filterCounters()" required
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] transition">
                        <option value="">Select unit</option>
                        @foreach($serviceUnits as $unit)
                            @php
                                $unitLabel = match($unit->code) {
                                    'REG' => 'Registrar',
                                    'ACC' => 'Accounts & Finance',
                                    'MED' => 'Medical Center',
                                    'CAF' => 'Cafeteria',
                                    'ITS' => 'IT Support',
                                    'LIB' => 'Library',
                                    default => $unit->name,
                                };
                            @endphp
                            <option value="{{ $unit->id }}" {{ (string) old('service_unit_id') === (string) $unit->id ? 'selected' : '' }}>{{ $unitLabel }}</option>
                        @endforeach
                    </select>
                    @error('service_unit_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="counter_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Counter Station</label>
                    <select id="counter_id" name="counter_id" x-model="counterId" required
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] transition">
                        <option value="">Select counter</option>
                        @foreach($serviceUnits as $unit)
                            @foreach($unit->counters as $counter)
                                @php
                                    $counterUnitLabel = match($unit->code) {
                                        'REG' => 'Registrar',
                                        'ACC' => 'Accounts & Finance',
                                        'MED' => 'Medical Center',
                                        'CAF' => 'Cafeteria',
                                        'ITS' => 'IT Support',
                                        'LIB' => 'Library',
                                        default => $unit->name,
                                    };
                                @endphp
                                <option data-unit="{{ $unit->id }}" value="{{ $counter->id }}" {{ (string) old('counter_id') === (string) $counter->id ? 'selected' : '' }}>{{ $counter->name }} — {{ $counterUnitLabel }}</option>
                            @endforeach
                        @endforeach
                    </select>
                    @error('counter_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="rank" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Rank / Role</label>
                    <select id="rank" name="rank" required
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] transition">
                        @foreach(['Counter Officer', 'Senior Officer', 'Desk Supervisor'] as $rank)
                            <option value="{{ $rank }}" {{ old('rank') === $rank ? 'selected' : '' }}>{{ $rank }}</option>
                        @endforeach
                    </select>
                    @error('rank') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="shift" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Shift Roster</label>
                    <select id="shift" name="shift" required
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] transition">
                        @foreach(['Morning', 'Day', 'Evening'] as $shift)
                            <option value="{{ $shift }}" {{ old('shift') === $shift ? 'selected' : '' }}>{{ $shift }}</option>
                        @endforeach
                    </select>
                    @error('shift') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="duty_contact" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Duty Contact Line</label>
                <input id="duty_contact" name="duty_contact" type="text" value="{{ old('duty_contact') }}" placeholder="01XXXXXXXXX" required
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] placeholder-slate-400 transition">
                @error('duty_contact') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="invite_code" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Invite Code</label>
                <input id="invite_code" name="invite_code" type="text" value="{{ old('invite_code') }}" placeholder="Issued by your supervisor" required
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm font-mono focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] placeholder-slate-400 transition">
                @error('invite_code') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="access_passcode" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Access Passcode</label>
                    <input id="access_passcode" name="access_passcode" type="password" x-model="strengthValue" @input="updateStrength()" required
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] transition">
                    <div class="mt-2 h-1.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                        <div class="h-full rounded-full transition-all" :class="strengthClass" :style="'width: ' + strengthWidth + '%'"></div>
                    </div>
                    @error('access_passcode') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="access_passcode_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Re-enter Passcode</label>
                    <input id="access_passcode_confirmation" name="access_passcode_confirmation" type="password" required
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] transition">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-full bg-[#00c389] hover:bg-[#00ab78] text-white font-black text-sm tracking-wide shadow-md shadow-emerald-500/25 hover:shadow-emerald-500/40 transition hover:scale-[1.02] active:scale-95 mt-2">
                Submit for Clearance
            </button>
        </form>

        <p class="text-center text-xs text-slate-500 dark:text-slate-400 pt-2">
            Already on the desk team?
            <a href="{{ route('operator.login') }}" class="font-bold text-[#00c389] hover:underline">Officer Sign In</a>
        </p>
    </div>
</div>

<script>
function operatorRegistration() {
    return {
        serviceUnitId: @json((string) old('service_unit_id', '')),
        counterId: @json((string) old('counter_id', '')),
        strengthValue: '',
        strengthWidth: 0,
        strengthClass: 'bg-slate-300',
        filterCounters() {
            this.counterId = '';
            document.querySelectorAll('#counter_id option[data-unit]').forEach(option => {
                option.hidden = option.dataset.unit !== this.serviceUnitId;
                option.disabled = option.dataset.unit !== this.serviceUnitId;
            });
        },
        updateStrength() {
            const value = this.strengthValue;
            let score = 0;
            if (value.length >= 8) score++;
            if (/[A-Z]/.test(value)) score++;
            if (/[0-9]/.test(value)) score++;
            if (/[^A-Za-z0-9]/.test(value)) score++;
            this.strengthWidth = [0, 25, 50, 75, 100][score];
            this.strengthClass = score < 2 ? 'bg-rose-400' : (score < 4 ? 'bg-amber-400' : 'bg-emerald-500');
        },
        init() {
            this.$nextTick(() => this.filterCounters());
        }
    }
}
</script>
@endsection
