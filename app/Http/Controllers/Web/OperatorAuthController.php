<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\OperatorRegistrationRequest;
use App\Models\Counter;
use App\Models\Department;
use App\Models\InviteCode;
use App\Models\OperatorProfile;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class OperatorAuthController extends Controller
{
    public function create(Request $request)
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        $request->session()->put('selected_role', 'operator');
        $serviceUnits = Department::with('counters')
            ->where('is_active', true)
            ->get();

        return view('auth.operator-register', compact('serviceUnits'));
    }

    public function store(OperatorRegistrationRequest $request)
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['officer_name'],
                'email' => $validated['official_mail'],
                'password' => Hash::make($validated['access_passcode']),
                'role' => 'COUNTER_OPERATOR',
                'phone' => $validated['duty_contact'],
                'is_active' => false,
                'account_status' => 'Pending',
            ]);

            $permissions = match ($validated['rank']) {
                'Desk Supervisor' => ['call_next', 'recall', 'start', 'complete', 'no_show', 'transfer', 'walk_in', 'toggle_status', 'manage_desk'],
                'Senior Officer' => ['call_next', 'recall', 'start', 'complete', 'no_show', 'transfer', 'walk_in', 'toggle_status'],
                default => ['call_next', 'recall', 'start', 'complete', 'no_show', 'toggle_status'],
            };

            OperatorProfile::create([
                'user_id' => $user->id,
                'badge_no' => $validated['badge_no'],
                'service_unit_id' => $validated['service_unit_id'],
                'counter_id' => $validated['counter_id'],
                'rank' => $validated['rank'],
                'shift' => $validated['shift'],
                'duty_contact' => $validated['duty_contact'],
                'permissions' => $permissions,
            ]);

            InviteCode::where('code', $validated['invite_code'])->lockForUpdate()->increment('used_count');

            AuditService::log('OPERATOR_ACCESS_REQUESTED', $user, 'User', $user->id, [
                'badge_no' => $validated['badge_no'],
                'service_unit_id' => $validated['service_unit_id'],
                'counter_id' => $validated['counter_id'],
                'rank' => $validated['rank'],
                'shift' => $validated['shift'],
            ]);

            return $user;
        });

        $request->session()->put('selected_role', 'operator');
        $request->session()->flash('clearance_pending', true);

        return redirect()->route('operator.clearance.pending');
    }

    public function pending(Request $request)
    {
        $request->session()->put('selected_role', 'operator');
        return view('auth.operator-clearance-pending');
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
