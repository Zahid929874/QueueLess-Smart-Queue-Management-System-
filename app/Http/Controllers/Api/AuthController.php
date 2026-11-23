<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Student Registration
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|string|email|max:150|unique:users',
            'student_id' => 'required|string|max:50|unique:student_profiles,student_id_number',
            'department' => 'required|string|max:100',
            'batch' => 'nullable|string|max:30',
            'phone' => 'nullable|string|max:25',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'STUDENT',
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
        ]);

        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'student_id_number' => $validated['student_id'],
            'department_name' => $validated['department'],
            'batch' => $validated['batch'] ?? '14th',
        ]);

        Auth::login($user);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        AuditService::log('USER_REGISTERED', $user, 'User', $user->id);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'student_profile' => $profile,
                ],
            ],
        ], 201);
    }

    /**
     * Login for all roles
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'login_role' => 'nullable|in:STUDENT,COUNTER_OPERATOR,ADMIN',
        ]);
        $loginRole = $credentials['login_role'] ?? null;
        unset($credentials['login_role']);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }
        $user = Auth::user();

        if ($loginRole && $user->role !== $loginRole) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['This account cannot use the selected sign-in page.'],
            ]);
        }

        if ($user->role === 'COUNTER_OPERATOR' && $user->account_status === 'Pending') {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['Your officer access is under review.'],
            ]);
        }

        if (!$request->expectsJson()
            && $user->role === 'ADMIN'
            && $user->adminProfile?->totp_enabled) {
            Auth::logout();
            $request->session()->put('admin_2fa_user_id', $user->id);
            $request->session()->put('selected_role', 'admin');
            return redirect()->route('admin.2fa');
        }

        AuditService::log('USER_LOGIN', $user, 'User', $user->id);

        $token = $user->createToken('auth-token')->plainTextToken;

        if (!$request->expectsJson()) {
            $request->session()->put('selected_role', match ($user->role) {
                'ADMIN' => 'admin',
                'COUNTER_OPERATOR' => 'operator',
                default => 'student',
            });
            $redirectRoute = match ($user->role) {
                'ADMIN' => 'admin.dashboard',
                'COUNTER_OPERATOR' => 'operator.console',
                default => 'student.dashboard',
            };
            return redirect()->route($redirectRoute);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'student_profile' => $user->studentProfile,
                    'active_assignment' => $user->activeAssignment?->load('counter.department'),
                ],
            ],
        ]);
    }

    /**
     * Current authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'student_profile' => $user->studentProfile,
                    'active_assignment' => $user->activeAssignment?->load('counter.department'),
                ],
            ],
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user) {
            AuditService::log('USER_LOGOUT', $user, 'User', $user->id);
        }

        Auth::guard('web')->logout();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}
