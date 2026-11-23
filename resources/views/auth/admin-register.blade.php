@extends('layouts.app', ['title' => 'First-Run Administrator Setup'])

@section('content')
<div x-data="adminSetupWizard()" class="max-w-lg mx-auto py-6 sm:py-8">
    <div class="bg-white dark:bg-[#0f172a] rounded-3xl p-8 sm:p-9 border border-slate-100 dark:border-slate-800 shadow-card space-y-6 interactive-card">

        <div class="text-center space-y-3">
            <a href="{{ route('home') }}" class="inline-block transition-transform hover:scale-105 active:scale-95" title="QueueLess">
                <img src="{{ asset('images/logo-light.png') }}" alt="QueueLess" class="dark:hidden h-12 sm:h-14 w-auto mx-auto object-contain drop-shadow-xs" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
                <img src="{{ asset('images/logo-transparent.png') }}" alt="QueueLess" class="hidden dark:block h-12 sm:h-14 w-auto mx-auto object-contain drop-shadow-xs" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
            </a>
            <p class="text-xs text-slate-500 dark:text-slate-400">First-Run Setup Wizard</p>
        </div>

        <div class="grid grid-cols-2 gap-2" aria-label="Setup progress">
            <button type="button" @click="goToStep(4)" class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-xs font-bold"
                    :class="step === 4 ? 'bg-[#00c389] text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'">
                <span class="w-6 h-6 rounded-full flex items-center justify-center border border-current">4</span>
                Administrator Account
            </button>
            <button type="button" @click="goToStep(5)" class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-xs font-bold"
                    :class="step === 5 ? 'bg-[#00c389] text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'">
                <span class="w-6 h-6 rounded-full flex items-center justify-center border border-current">5</span>
                2FA Security
            </button>
        </div>

        <form method="POST" action="{{ route('admin.register.post') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="invite_token" value="{{ $token }}">

            <section x-show="step === 4" x-cloak class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="admin_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Administrator Name</label>
                        <input id="admin_name" name="admin_name" type="text" value="{{ old('admin_name') }}" required
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] transition">
                        @error('admin_name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="employee_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Authority ID</label>
                        <input id="employee_id" name="employee_id" type="text" value="{{ old('employee_id') }}" placeholder="ADM-001" required
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm font-mono focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] placeholder-slate-400 transition">
                        @error('employee_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="institutional_mail" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Institutional Mail</label>
                    <input id="institutional_mail" name="institutional_mail" type="email"
                           value="{{ old('institutional_mail', $invite->institutional_mail) }}" required
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] transition">
                    @error('institutional_mail') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="duty_contact" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Secure Contact Line</label>
                    <input id="duty_contact" name="duty_contact" type="text" value="{{ old('duty_contact') }}" placeholder="01XXXXXXXXX" required
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] placeholder-slate-400 transition">
                    <p class="mt-1 text-[10px] text-slate-400">Used for OTP and emergency contact</p>
                    @error('duty_contact') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="office_title" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Office Title <span class="font-normal text-slate-400">(optional)</span></label>
                    <input id="office_title" name="office_title" type="text" value="{{ old('office_title') }}" placeholder="e.g. Registrar"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] placeholder-slate-400 transition">
                    @error('office_title') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Profile Photo <span class="font-normal text-slate-400">(optional)</span></label>
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                            <img x-show="photoPreview" :src="photoPreview" alt="Profile preview" class="w-full h-full object-cover">
                            <i x-show="!photoPreview" data-lucide="user-round" class="w-7 h-7 text-slate-400"></i>
                        </div>
                        <input id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp"
                               @change="previewPhoto"
                               class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-full file:border-0 file:px-4 file:py-2 file:bg-slate-100 file:text-slate-700 dark:file:bg-slate-800 dark:file:text-slate-200">
                    </div>
                    <p class="mt-1 text-[10px] text-slate-400">JPG, PNG or WebP, maximum 2 MB</p>
                    @error('profile_photo') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Master Passcode</label>
                        <div class="relative">
                            <input id="password" name="password" :type="showPasscode ? 'text' : 'password'" x-model="passcode" @input="updateStrength" required
                                   class="w-full px-4 pr-11 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] transition">
                            <button type="button" @click="showPasscode = !showPasscode" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" :title="showPasscode ? 'Hide passcode' : 'Show passcode'">
                                <i :data-lucide="showPasscode ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <div class="mt-2 h-1.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                            <div class="h-full rounded-full transition-all" :class="strengthClass" :style="'width:' + strengthWidth + '%'"></div>
                        </div>
                        <p class="mt-1 text-[10px]" :class="passcodeValid ? 'text-emerald-600' : 'text-slate-400'">
                            12+ chars, upper, lower, number & symbol
                        </p>
                        @error('password') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Re-enter Master Passcode</label>
                        <div class="relative">
                            <input id="password_confirmation" name="password_confirmation" :type="showPasscodeConfirmation ? 'text' : 'password'" required
                                   class="w-full px-4 pr-11 py-2.5 rounded-xl bg-slate-50 dark:bg-[#142034] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389] transition">
                            <button type="button" @click="showPasscodeConfirmation = !showPasscodeConfirmation" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" :title="showPasscodeConfirmation ? 'Hide passcode' : 'Show passcode'">
                                <i :data-lucide="showPasscodeConfirmation ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="button" @click="generatePasscode" class="w-full px-4 py-2.5 rounded-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-[#142034] text-xs font-bold text-slate-700 dark:text-slate-200 flex items-center justify-center gap-2">
                    <i data-lucide="key-round" class="w-4 h-4 text-[#00c389]"></i>
                    Generate Strong Passcode
                </button>

                <button type="button" @click="goToStep(5)" class="w-full py-3.5 rounded-full bg-[#00c389] hover:bg-[#00ab78] text-white font-black text-sm tracking-wide shadow-md shadow-emerald-500/25 transition">
                    Continue to 2FA
                </button>
            </section>

            <section x-show="step === 5" x-cloak class="space-y-4">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-[#142034]/70 p-4">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white mb-3">Administrator Review</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div><dt class="text-slate-400">Administrator Name</dt><dd class="mt-0.5 font-bold text-slate-800 dark:text-slate-200" x-text="document.querySelector('[name=admin_name]').value || 'Not provided'"></dd></div>
                        <div><dt class="text-slate-400">Authority ID</dt><dd class="mt-0.5 font-bold font-mono text-slate-800 dark:text-slate-200" x-text="document.querySelector('[name=employee_id]').value || 'Not provided'"></dd></div>
                        <div><dt class="text-slate-400">Institutional Mail</dt><dd class="mt-0.5 font-bold text-slate-800 dark:text-slate-200" x-text="document.querySelector('[name=institutional_mail]').value || 'Not provided'"></dd></div>
                        <div><dt class="text-slate-400">Secure Contact Line</dt><dd class="mt-0.5 font-bold text-slate-800 dark:text-slate-200" x-text="document.querySelector('[name=duty_contact]').value || 'Not provided'"></dd></div>
                        <div><dt class="text-slate-400">Office Title</dt><dd class="mt-0.5 font-bold text-slate-800 dark:text-slate-200" x-text="document.querySelector('[name=office_title]').value || 'Not provided'"></dd></div>
                        <div><dt class="text-slate-400">Profile Photo</dt><dd class="mt-0.5 font-bold text-slate-800 dark:text-slate-200" x-text="photoName || 'Not provided'"></dd></div>
                    </dl>
                    <p class="mt-3 text-[10px] text-slate-400">Master Passcode is hidden from the review summary.</p>
                </div>

                <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-[#142034]/70 p-4 space-y-3">
                    <div>
                        <h3 class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="shield-check" class="w-4 h-4 text-[#00c389]"></i>
                            TOTP Setup
                        </h3>
                        <p class="mt-1 text-[10px] leading-4 text-slate-500 dark:text-slate-400">Add QueueLess to an authenticator app, then enter the six-digit code.</p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Authenticator Setup Key</span>
                        <code class="block w-full px-3 py-2.5 rounded-xl bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold text-slate-700 dark:text-slate-300 break-all">{{ $totpSecret }}</code>
                    </div>
                    <div>
                        <label for="totp_code" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">TOTP Verification Code</label>
                        <input id="totp_code" name="totp_code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" placeholder="123456" required
                               class="w-full px-4 py-2.5 rounded-xl bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm font-mono tracking-[0.35em] text-center focus:outline-none focus:border-[#00c389] focus:ring-1 focus:ring-[#00c389]">
                        @error('totp_code') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="rounded-xl border border-amber-200 bg-amber-50 dark:bg-amber-950/20 dark:border-amber-900/60 p-3">
                        <p class="text-[10px] font-black text-amber-800 dark:text-amber-300 uppercase tracking-wider">Recovery Codes</p>
                        <p class="mt-1 text-[10px] text-amber-700 dark:text-amber-400">Save these codes securely. Losing the authenticator and recovery codes can lock the system.</p>
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            @foreach($recoveryCodes as $code)
                                <code class="px-2 py-1.5 rounded-lg bg-white dark:bg-[#0f172a] border border-amber-200 dark:border-amber-900/60 text-center text-[10px] font-mono font-bold text-slate-700 dark:text-slate-300">{{ $code }}</code>
                            @endforeach
                        </div>
                    </div>
                </section>

                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click="goToStep(4)" class="py-3 rounded-full border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold">Back</button>
                    <button type="submit" class="py-3 rounded-full bg-[#00c389] hover:bg-[#00ab78] text-white text-xs font-black">Activate Administrator</button>
                </div>
            </section>
        </form>

        <p class="text-center text-xs text-slate-500 dark:text-slate-400 pt-2">
            <a href="{{ route('admin.login') }}" class="font-bold text-[#00c389] hover:underline">Back to Admin Sign In</a>
        </p>
    </div>
</div>

<script>
function adminSetupWizard() {
    return {
        step: 4,
        passcode: '',
        showPasscode: false,
        showPasscodeConfirmation: false,
        strengthWidth: 0,
        strengthClass: 'bg-slate-300',
        passcodeValid: false,
        photoPreview: null,
        photoName: '',
        goToStep(step) {
            this.step = step;
        },
        previewPhoto(event) {
            const file = event.target.files[0];
            if (!file) {
                this.photoPreview = null;
                this.photoName = '';
                return;
            }
            this.photoName = file.name;
            const reader = new FileReader();
            reader.onload = e => { this.photoPreview = e.target.result; };
            reader.readAsDataURL(file);
        },
        updateStrength() {
            let score = 0;
            if (this.passcode.length >= 12) score++;
            if (/[A-Z]/.test(this.passcode) && /[a-z]/.test(this.passcode)) score++;
            if (/[0-9]/.test(this.passcode)) score++;
            if (/[^A-Za-z0-9]/.test(this.passcode)) score++;
            this.passcodeValid = score === 4;
            this.strengthWidth = [0, 25, 50, 75, 100][score];
            this.strengthClass = score < 2 ? 'bg-rose-400' : (score < 4 ? 'bg-amber-400' : 'bg-emerald-500');
        },
        generatePasscode() {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%&*';
            const values = new Uint32Array(18);
            crypto.getRandomValues(values);
            this.passcode = Array.from(values, value => chars[value % chars.length]).join('');
            const confirmation = document.querySelector('[name=password_confirmation]');
            if (confirmation) confirmation.value = this.passcode;
            this.updateStrength();
        }
    }
}
</script>
@endsection
