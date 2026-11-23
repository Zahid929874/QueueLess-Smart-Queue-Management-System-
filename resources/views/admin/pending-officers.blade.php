@extends('layouts.app', ['title' => 'Pending Officers'])

@section('content')
<div class="space-y-5">
    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-card">
        <h1 class="text-lg font-black text-slate-900 dark:text-white">Pending Officers</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Review operator clearance requests.</p>
    </div>

    @foreach($pendingOfficers as $officer)
        <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-card">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ $officer->name }}</h2>
                    <p class="text-xs text-slate-500">{{ $officer->email }} · {{ $officer->operatorProfile?->badge_no }}</p>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ $officer->operatorProfile?->serviceUnit?->name }} ·
                        {{ $officer->operatorProfile?->counter?->name }} ·
                        {{ $officer->operatorProfile?->rank }} ·
                        {{ $officer->operatorProfile?->shift }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <form method="POST" action="{{ route('admin.pending-officers.approve', $officer) }}">
                        @csrf
                        <button class="px-4 py-2 rounded-full bg-emerald-500 text-white text-xs font-bold">Approve</button>
                    </form>
                    <form method="POST" action="{{ route('admin.pending-officers.reject', $officer) }}" class="flex items-center gap-2">
                        @csrf
                        <input name="reason" required placeholder="Reason" class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs bg-slate-50 dark:bg-[#142034]">
                        <button class="px-4 py-2 rounded-full bg-rose-500 text-white text-xs font-bold">Reject</button>
                    </form>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.pending-officers.update', $officer) }}" class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">Account Status</label>
                        <select name="account_status" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs bg-slate-50 dark:bg-[#142034]">
                            @foreach(['Pending', 'Active', 'Inactive', 'Suspended'] as $status)
                                <option value="{{ $status }}" {{ $officer->account_status === $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">Permissions</span>
                        <div class="grid grid-cols-2 gap-1.5">
                            @foreach(['call_next', 'recall', 'start', 'complete', 'no_show', 'transfer', 'walk_in', 'toggle_status', 'manage_desk'] as $permission)
                                <label class="text-[10px] text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission }}"
                                           {{ in_array($permission, $officer->operatorProfile?->permissions ?? []) ? 'checked' : '' }}>
                                    {{ str_replace('_', ' ', ucfirst($permission)) }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <button class="mt-3 px-4 py-2 rounded-full bg-slate-900 text-white text-xs font-bold">Save Status & Permissions</button>
            </form>
        </div>
    @endforeach
</div>
@endsection
