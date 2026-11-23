<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\DisplayController;
use App\Http\Controllers\Api\OperatorController;
use App\Http\Controllers\Api\QueueController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for QueueLess
|--------------------------------------------------------------------------
*/

// Authentication
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum,web');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum,web');
});

// Public Departments & Services
Route::get('/departments', [DepartmentController::class, 'index']);
Route::get('/departments/{id}', [DepartmentController::class, 'show']);
Route::get('/departments/{id}/services', [DepartmentController::class, 'services']);

// Public TV Digital Signage
Route::get('/display/live/{department?}', [DisplayController::class, 'live']);
Route::get('/display/stream/{department?}', [DisplayController::class, 'stream']);

// Protected Student Queue Routes
Route::middleware(['auth:sanctum,web'])->prefix('queues')->group(function () {
    Route::post('/join', [QueueController::class, 'join']);
    Route::get('/active', [QueueController::class, 'active']);
    Route::get('/history', [QueueController::class, 'history']);
    Route::get('/{id}', [QueueController::class, 'show']);
    Route::post('/{id}/cancel', [QueueController::class, 'cancel']);
});

// Protected Counter Operator Routes
Route::middleware(['auth:sanctum,web', 'role:COUNTER_OPERATOR,ADMIN'])->prefix('operator')->group(function () {
    Route::get('/counters', [OperatorController::class, 'counters']);
    Route::post('/assign-counter', [OperatorController::class, 'assignCounter']);
    Route::get('/queue', [OperatorController::class, 'queue']);
    Route::post('/call-next', [OperatorController::class, 'callNext']);
    Route::post('/recall', [OperatorController::class, 'recall']);
    Route::post('/start', [OperatorController::class, 'start']);
    Route::post('/complete', [OperatorController::class, 'complete']);
    Route::post('/no-show', [OperatorController::class, 'noShow']);
    Route::post('/transfer', [OperatorController::class, 'transfer']);
    Route::post('/walk-in', [OperatorController::class, 'walkInTicket']);
    Route::post('/toggle-status', [OperatorController::class, 'toggleStatus']);
});

// Protected Administrator Routes
Route::middleware(['auth:sanctum,web', 'role:ADMIN'])->prefix('admin')->group(function () {
    Route::get('/metrics', [AdminController::class, 'metrics']);
    Route::get('/hourly', [AdminController::class, 'hourly']);
    Route::get('/heatmap', [AdminController::class, 'heatmap']);
    Route::get('/recommendations', [AdminController::class, 'recommendations']);
    Route::get('/queues', [AdminController::class, 'liveQueues']);
    Route::post('/departments', [AdminController::class, 'createDepartment']);
    Route::post('/departments/{id}/toggle', [AdminController::class, 'toggleDepartment']);
    Route::post('/counters', [AdminController::class, 'createCounter']);
    Route::post('/counters/{id}/toggle-status', [AdminController::class, 'toggleCounterStatus']);
    Route::post('/services', [AdminController::class, 'createService']);
    Route::post('/reset-queues', [AdminController::class, 'resetQueues']);
    Route::get('/export-csv', [AdminController::class, 'exportCsv']);
});
