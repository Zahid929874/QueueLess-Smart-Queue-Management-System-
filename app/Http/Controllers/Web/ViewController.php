<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use App\Models\Department;
use App\Models\QueueEvent;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Services\QueuePredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ViewController extends Controller
{
    public function __construct(
        protected QueuePredictionService $predictionService
    ) {}

    /**
     * Landing page / portal
     */
    public function welcome()
    {
        $departments = Department::with(['services', 'counters'])->where('is_active', true)->get();
        return view('welcome', compact('departments'));
    }

    /**
     * Login view
     */
    public function login()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return match (session('selected_role')) {
            'operator' => redirect()->route('operator.login'),
            'admin' => redirect()->route('admin.login'),
            default => redirect()->route('student.login'),
        };
    }

    public function studentLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        session(['selected_role' => 'student']);
        return view('auth.student-login');
    }

    public function operatorLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        session(['selected_role' => 'operator']);
        return view('auth.operator-login');
    }

    public function adminLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        session(['selected_role' => 'admin']);
        return view('auth.admin-login');
    }

    /**
     * Register view
     */
    public function register()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        if (request()->query('role') === 'operator'
            || session('selected_role') === 'operator'
            || request()->is('operator/*')) {
            session(['selected_role' => 'operator']);
            return redirect()->route('operator.register');
        }

        return view('auth.register');
    }

    /**
     * Student Dashboard (Live Ticket Tracker)
     */
    public function studentDashboard(Request $request)
    {
        $user = $request->user();
        $studentProfile = $user->studentProfile;

        $activeTicket = null;
        $peopleAhead = 0;
        $position = 1;
        $currentServing = null;
        $activeCounters = 0;

        if ($studentProfile) {
            $activeTicket = QueueTicket::with(['department', 'service', 'counter'])
                ->where('student_profile_id', $studentProfile->id)
                ->whereIn('status', ['WAITING', 'CALLED', 'SERVING'])
                ->latest()
                ->first();

            if ($activeTicket) {
                if ($activeTicket->status === 'WAITING') {
                    $peopleAhead = QueueTicket::where('department_id', $activeTicket->department_id)
                        ->where('status', 'WAITING')
                        ->where('id', '<', $activeTicket->id)
                        ->count();

                    $position = $peopleAhead + 1;

                    $pred = $this->predictionService->estimateWaitTime($activeTicket->department, $activeTicket->service, $peopleAhead);
                    $activeTicket->estimated_wait_time_min = $pred['estimated_wait_min'];
                    $activeCounters = $pred['active_counters'];
                } else {
                    $activeCounters = $activeTicket->department->counters()->whereIn('status', ['AVAILABLE', 'BUSY'])->count();
                }

                $currentServing = QueueTicket::where('department_id', $activeTicket->department_id)
                    ->whereIn('status', ['CALLED', 'SERVING'])
                    ->latest('called_at')
                    ->first();
            }
        }

        $departments = Department::where('is_active', true)->get();

        return view('student.dashboard', compact('user', 'studentProfile', 'activeTicket', 'peopleAhead', 'position', 'currentServing', 'activeCounters', 'departments'));
    }

    /**
     * Student Join Queue Page
     */
    public function studentJoin(Request $request)
    {
        $departments = Department::with(['services' => function ($q) {
            $q->where('is_active', true);
        }])->where('is_active', true)->get();

        $selectedDeptId = $request->query('dept_id');

        return view('student.join', compact('departments', 'selectedDeptId'));
    }

    /**
     * Student Ticket History
     */
    public function studentHistory(Request $request)
    {
        $user = $request->user();
        $profile = $user->studentProfile;

        $tickets = $profile
            ? QueueTicket::with(['department', 'service', 'counter'])
                ->where('student_profile_id', $profile->id)
                ->latest()
                ->paginate(10)
            : collect();

        return view('student.history', compact('user', 'tickets'));
    }

    /**
     * Operator overview dashboard
     */
    public function operatorDashboard(Request $request)
    {
        $user = $request->user();
        $departments = Department::where('is_active', true)
            ->with(['counters.currentAssignment.user'])
            ->withCount([
                'queueTickets as waiting_count' => fn ($query) => $query->where('status', 'WAITING'),
                'counters as active_counters_count' => fn ($query) => $query->whereIn('status', ['AVAILABLE', 'BUSY']),
                'counters as total_counters_count',
            ])
            ->orderBy('name')
            ->get();

        $departmentIds = $departments->modelKeys();
        $ticketQuery = QueueTicket::whereIn('department_id', $departmentIds);

        $waitingTickets = (clone $ticketQuery)->where('status', 'WAITING')->count();
        $averageWaitMinutes = (clone $ticketQuery)
            ->where('status', 'WAITING')
            ->avg('estimated_wait_time_min');
        $inServiceTickets = (clone $ticketQuery)->whereIn('status', ['CALLED', 'SERVING'])->count();
        $servedToday = (clone $ticketQuery)
            ->where('status', 'COMPLETED')
            ->whereDate('completed_at', today())
            ->count();

        $recentActivity = QueueEvent::with([
            'ticket.department',
            'ticket.service',
            'ticket.studentProfile.user',
            'actor',
        ])
            ->whereHas('ticket', fn ($query) => $query->whereIn('department_id', $departmentIds))
            ->latest('created_at')
            ->limit(8)
            ->get();

        return view('operator.dashboard', compact(
            'user',
            'departments',
            'waitingTickets',
            'averageWaitMinutes',
            'inServiceTickets',
            'servedToday',
            'recentActivity',
        ));
    }

    /**
     * Operator Console
     */
    public function operatorConsole(Request $request)
    {
        $user = $request->user();
        $assignment = $user->activeAssignment?->load('counter.department.services');
        $allDepartments = Department::with(['counters', 'services'])
            ->withCount([
                'queueTickets as waiting_applications_count' => function ($query) {
                    $query->where('status', 'WAITING');
                },
            ])
            ->where('is_active', true)
            ->get();

        $currentTicket = null;
        $waitingQueue = collect();

        if ($assignment) {
            $counter = $assignment->counter;
            $department = $counter->department;

            $currentTicket = QueueTicket::with(['service', 'studentProfile.user', 'session'])
                ->where('counter_id', $counter->id)
                ->whereIn('status', ['CALLED', 'SERVING'])
                ->latest('called_at')
                ->first();

            $waitingQueue = QueueTicket::with(['service', 'studentProfile.user'])
                ->where('department_id', $department->id)
                ->where('status', 'WAITING')
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return view('operator.console', compact('user', 'assignment', 'allDepartments', 'currentTicket', 'waitingQueue'));
    }

    /**
     * Admin Dashboard
     */
    public function adminDashboard()
    {
        $departments = Department::with('counters')->where('is_active', true)->get();
        $recommendations = $this->predictionService->generateCapacityRecommendations();

        return view('admin.dashboard', compact('departments', 'recommendations'));
    }

    /**
     * Public Digital Signage
     */
    public function publicDisplay(?string $department = null)
    {
        $selectedDepartment = null;
        if ($department) {
            $selectedDepartment = Department::where('code', strtoupper($department))->first();
        }

        $allDepartments = Department::where('is_active', true)->get();

        return view('display.tv', [
            'departmentCode' => $department ? strtoupper($department) : null,
            'selectedDepartment' => $selectedDepartment,
            'allDepartments' => $allDepartments,
        ]);
    }

    /**
     * Helper to route users after login
     */
    protected function redirectBasedOnRole($user)
    {
        return match ($user->role) {
            'ADMIN' => redirect()->route('admin.dashboard'),
            'COUNTER_OPERATOR' => redirect()->route('operator.console'),
            default => redirect()->route('student.dashboard'),
        };
    }
}
