<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class PendingOfficerController extends Controller
{
    public function index()
    {
        $pendingOfficers = User::where('role', 'COUNTER_OPERATOR')
            ->where('account_status', 'Pending')
            ->with('operatorProfile.serviceUnit', 'operatorProfile.counter')
            ->latest()
            ->get();

        return view('admin.pending-officers', compact('pendingOfficers'));
    }

    public function approve(User $user)
    {
        $user->update([
            'account_status' => 'Active',
            'is_active' => true,
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Officer account approved.');
    }

    public function reject(Request $request, User $user)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $user->update([
            'account_status' => 'Inactive',
            'is_active' => false,
            'rejection_reason' => $validated['reason'],
        ]);

        return back()->with('success', 'Officer request rejected.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'account_status' => 'required|in:Pending,Active,Inactive,Suspended',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|max:50',
        ]);

        $status = $validated['account_status'];
        $user->update([
            'account_status' => $status,
            'is_active' => $status === 'Active',
        ]);
        $user->operatorProfile?->update([
            'permissions' => $validated['permissions'] ?? [],
        ]);

        return back()->with('success', 'Officer status and permissions updated.');
    }
}
