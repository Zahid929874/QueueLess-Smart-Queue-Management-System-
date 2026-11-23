<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminRegistrationRequest;
use App\Http\Requests\VerifyAdminInviteRequest;
use App\Models\AdminInvite;
use App\Models\AdminProfile;
use App\Models\Department;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    private function adminRegistrationAvailable(): bool
    {
        return !User::where('role', 'ADMIN')->exists();
    }

    public function gate()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.admin-register-gate', [
            'registrationAvailable' => $this->adminRegistrationAvailable(),
        ]);
    }

    public function verify(VerifyAdminInviteRequest $request)
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        if (!$this->adminRegistrationAvailable()) {
            return back()->withErrors([
                'invite_token' => 'Admin registration is closed. Only one administrator account is allowed.',
            ]);
        }

        $ip = $request->ip();
        $key = 'admin-register-attempt:' . $ip;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withInput()->withErrors([
                'invite_token' => 'Too many attempts. Try again in ' . $seconds . ' seconds.',
            ]);
        }

        $token = $request->validated()['invite_token'];
        $invite = AdminInvite::where('token_hash', hash('sha256', $token))->first();

        if (!$invite || !$invite->isValid()) {
            RateLimiter::hit($key, 60);
            AuditService::log('ADMIN_INVITE_FAILED', null, 'AdminInvite', null, [
                'reason' => 'invalid_expired_or_used',
                'ip_address' => $ip,
            ]);
            return back()->withInput()->withErrors([
                'invite_token' => 'Invalid or expired invite',
            ]);
        }

        RateLimiter::clear($key);
        return redirect()->route('admin.register.form', ['token' => $token]);
    }

    public function create(Request $request, string $token)
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        if (!$this->adminRegistrationAvailable()) {
            abort(403);
        }

        $invite = AdminInvite::where('token_hash', hash('sha256', $token))->first();
        if (!$invite || !$invite->isValid()) {
            abort(404);
        }

        $serviceUnits = Department::where('is_active', true)
            ->orderBy('name')
            ->get();

        $google2fa = new Google2FA();
        $totpSecret = $google2fa->generateSecretKey();
        $recoveryCodes = collect(range(1, 10))->map(function () {
            return strtoupper(Str::random(4) . '-' . Str::random(4));
        })->all();

        $request->session()->put('admin_totp_secret', $totpSecret);
        $request->session()->put('admin_recovery_codes', $recoveryCodes);

        return view('auth.admin-register', [
            'invite' => $invite,
            'token' => $token,
            'serviceUnits' => $serviceUnits,
            'totpSecret' => $totpSecret,
            'recoveryCodes' => $recoveryCodes,
            'totpUri' => $google2fa->getQRCodeUrl(
                'QueueLess',
                $invite->institutional_mail ?: 'admin@queueless.local',
                $totpSecret
            ),
        ]);
    }

    public function store(AdminRegistrationRequest $request)
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        $validated = $request->validated();
        $invite = AdminInvite::where('token_hash', hash('sha256', $validated['invite_token']))->first();

        if (!$this->adminRegistrationAvailable()) {
            return back()->withInput()->withErrors([
                'invite_token' => 'Admin registration is closed. Only one administrator account is allowed.',
            ]);
        }

        if (!$invite || !$invite->isValid()) {
            return back()->withInput()->withErrors([
                'invite_token' => 'Invalid or expired invite',
            ]);
        }

        $totpSecret = $request->session()->get('admin_totp_secret');
        if (!$totpSecret) {
            return back()->withInput()->withErrors([
                'totp_code' => 'TOTP setup session expired. Refresh the registration page and try again.',
            ]);
        }

        $google2fa = new Google2FA();
        if (!$google2fa->verifyKey($totpSecret, $validated['totp_code'])) {
            return back()->withInput()->withErrors([
                'totp_code' => 'The TOTP code is invalid.',
            ]);
        }

        $plainRecoveryCodes = $request->session()->get('admin_recovery_codes', []);
        $recoveryCodes = array_map(
            fn (string $code) => Hash::make($code),
            $plainRecoveryCodes
        );

        $profilePhotoPath = null;
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('admin-profiles', 'public');
        }

        $user = DB::transaction(function () use ($validated, $invite, $totpSecret, $recoveryCodes, $profilePhotoPath) {
            $user = User::create([
                'name' => $validated['admin_name'],
                'email' => $validated['institutional_mail'],
                'password' => Hash::make($validated['password']),
                'role' => 'ADMIN',
                'phone' => $validated['duty_contact'],
                'is_active' => true,
                'account_status' => 'Active',
            ]);

            AdminProfile::create([
                'user_id' => $user->id,
                'employee_id' => $validated['employee_id'],
                'duty_contact' => $validated['duty_contact'],
                'office_title' => $validated['office_title'] ?? null,
                'profile_photo_path' => $profilePhotoPath,
                'tier' => 'Super Admin',
                'managed_service_unit_ids' => null,
                'totp_secret' => $totpSecret,
                'recovery_codes' => $recoveryCodes,
                'totp_enabled' => true,
            ]);

            $invite->update([
                'used_at' => now(),
                'used_by' => $user->id,
            ]);

            AuditService::log('ADMIN_REGISTERED', $user, 'User', $user->id, [
                'tier' => 'Super Admin',
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->put('selected_role', 'admin');
        $request->session()->forget(['admin_totp_secret', 'admin_recovery_codes']);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function challenge(Request $request)
    {
        if (!$request->session()->has('admin_2fa_user_id')) {
            return redirect()->route('admin.login');
        }

        return view('auth.admin-2fa');
    }

    public function verifyTwoFactor(Request $request)
    {
        $validated = $request->validate([
            'two_factor_code' => ['required', 'string', 'max:20'],
        ]);

        $userId = $request->session()->get('admin_2fa_user_id');
        $user = $userId ? User::with('adminProfile')->find($userId) : null;
        $profile = $user?->adminProfile;

        if (!$user || !$profile || !$profile->totp_enabled || !$profile->totp_secret) {
            $request->session()->forget('admin_2fa_user_id');
            throw ValidationException::withMessages([
                'two_factor_code' => ['Two-factor session expired. Please sign in again.'],
            ]);
        }

        $code = strtoupper(trim($validated['two_factor_code']));
        $valid = (new Google2FA())->verifyKey($profile->totp_secret, $code);

        if (!$valid) {
            $remainingCodes = $profile->recovery_codes ?? [];
            foreach ($remainingCodes as $index => $hashedCode) {
                if (Hash::check($code, $hashedCode)) {
                    unset($remainingCodes[$index]);
                    $profile->update(['recovery_codes' => array_values($remainingCodes)]);
                    $valid = true;
                    break;
                }
            }
        }

        if (!$valid) {
            throw ValidationException::withMessages([
                'two_factor_code' => ['Invalid authenticator or recovery code.'],
            ]);
        }

        Auth::login($user);
        $request->session()->forget('admin_2fa_user_id');
        $request->session()->put('selected_role', 'admin');
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    private function redirectBasedOnRole(User $user)
    {
        return match ($user->role) {
            'ADMIN' => redirect()->route('admin.dashboard'),
            'COUNTER_OPERATOR' => redirect()->route('operator.console'),
            default => redirect()->route('student.dashboard'),
        };
    }
}
