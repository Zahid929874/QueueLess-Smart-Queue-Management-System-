<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Web\ViewController;
use App\Http\Controllers\Web\OperatorAuthController;
use App\Http\Controllers\Web\PendingOfficerController;
use App\Http\Controllers\Web\AdminAuthController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes for QueueLess
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});
Route::get('/welcome', [ViewController::class, 'welcome'])->name('home');

// Authentication Web Routes
Route::get('/login', [ViewController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/student/login', [ViewController::class, 'studentLogin'])->name('student.login');
Route::get('/operator/login', [ViewController::class, 'operatorLogin'])->name('operator.login');
Route::get('/admin/login', [ViewController::class, 'adminLogin'])->name('admin.login');
Route::get('/admin/2fa', [AdminAuthController::class, 'challenge'])->name('admin.2fa');
Route::post('/admin/2fa', [AdminAuthController::class, 'verifyTwoFactor'])->name('admin.2fa.verify');

Route::get('/register', [ViewController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/operator/register', [\App\Http\Controllers\Web\OperatorAuthController::class, 'create'])->name('operator.register');
Route::post('/operator/register', [\App\Http\Controllers\Web\OperatorAuthController::class, 'store'])->name('operator.register.post');
Route::get('/operator/clearance-pending', [OperatorAuthController::class, 'pending'])->name('operator.clearance.pending');
Route::get('/admin/register', [AdminAuthController::class, 'gate'])->name('admin.register');
Route::post('/admin/register', [AdminAuthController::class, 'verify'])->name('admin.register.verify');
Route::get('/admin/register/{token}', [AdminAuthController::class, 'create'])->name('admin.register.form');
Route::post('/admin/register/complete', [AdminAuthController::class, 'store'])->name('admin.register.post');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home');
})->name('logout');

// Quick Persona Switcher for Evaluation
Route::get('/demo-login/{role}', function (string $role) {
    $roleKey = strtolower($role);
    request()->session()->put('selected_role', match ($roleKey) {
        'admin' => 'admin',
        'operator', 'operator-reg' => 'operator',
        default => 'student',
    });

    $email = match (strtolower($role)) {
        'admin' => 'admin@queueless.local',
        'operator' => 'acc.operator@queueless.local',
        'operator-reg' => 'reg.operator@queueless.local',
        'student' => 'student1@queueless.local',
        'student2' => 'student2@queueless.local',
        default => 'student1@queueless.local',
    };

    $legacyEmail = str_replace('@queueless.local', '@baust.edu.bd', $email);
    $user = User::where('email', $email)->orWhere('email', $legacyEmail)->first();
    if ($user) {
        Auth::login($user);
        request()->session()->regenerate();
        return match ($user->role) {
            'ADMIN' => redirect()->route('admin.dashboard'),
            'COUNTER_OPERATOR' => redirect()->route('operator.console'),
            default => redirect()->route('student.dashboard'),
        };
    }
    return redirect()->route('login');
})->name('demo.login');

// Public TV Display Routes
Route::get('/display/{department?}', [ViewController::class, 'publicDisplay'])->name('public.display');

// Protected Student Routes
Route::middleware(['auth', 'role:STUDENT,ADMIN'])->prefix('student')->group(function () {
    Route::get('/dashboard', [ViewController::class, 'studentDashboard'])->name('student.dashboard');
    Route::get('/join', [ViewController::class, 'studentJoin'])->name('student.join');
    Route::get('/history', [ViewController::class, 'studentHistory'])->name('student.history');
});

// Protected Operator Routes
Route::middleware(['auth', 'role:COUNTER_OPERATOR,ADMIN'])->prefix('operator')->group(function () {
    Route::get('/console', [ViewController::class, 'operatorConsole'])->name('operator.console');
    Route::get('/', [ViewController::class, 'operatorConsole']);
});

// Protected Admin Routes
Route::middleware(['auth', 'role:ADMIN'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [ViewController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/', [ViewController::class, 'adminDashboard']);
    Route::get('/export-csv', [AdminController::class, 'exportCsv'])->name('admin.export-csv');
    Route::get('/pending-officers', [PendingOfficerController::class, 'index'])->name('admin.pending-officers');
    Route::post('/pending-officers/{user}/approve', [PendingOfficerController::class, 'approve'])->name('admin.pending-officers.approve');
    Route::post('/pending-officers/{user}/reject', [PendingOfficerController::class, 'reject'])->name('admin.pending-officers.reject');
    Route::post('/pending-officers/{user}/update', [PendingOfficerController::class, 'update'])->name('admin.pending-officers.update');
});
