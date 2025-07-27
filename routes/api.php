<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\ComplaintStatusController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Public routes - no authentication required
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// Protected routes - require authentication
Route::middleware(['auth:sanctum'])->group(function () {
    // Authentication
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    
    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    
    // Complaints
    Route::apiResource('complaints', ComplaintController::class);
    Route::post('/complaints/{complaint}/assign', [ComplaintController::class, 'assign']);
    Route::post('/complaints/{complaint}/resolve', [ComplaintController::class, 'resolve']);
    
    // Users
    Route::apiResource('users', UserController::class);
    Route::get('/users/technicians', [UserController::class, 'technicians']);
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
    
    // Complaint Statuses
    Route::get('/complaint-statuses', [ComplaintStatusController::class, 'index']);
});
