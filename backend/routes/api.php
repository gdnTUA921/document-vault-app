<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\DashboardController; 
use App\Http\Controllers\UserKeyController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FileShareController;
use App\Http\Controllers\SearchController;
use App\Http\Middleware\RoleMiddleware;

// Test Route
Route::get('/hello', function () {
    return response()->json(['message' => 'Hello from Laravel!']);
});

// Authentication Routes
Route::post('register', [AuthController::class,'register']);
Route::post('login', [AuthController::class,'login'])
    ->middleware('throttle:10,1'); // 10 attempts per minute

// Protected Routes (JWT required)
Route::group(['middleware' => ['auth:api']], function() {
    Route::post('logout', [AuthController::class,'logout']);
    Route::get('me', [AuthController::class,'me']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::post('refresh', [AuthController::class,'refresh']);

    // RSA User Keys
    Route::post('/keys/generate', [UserKeyController::class, 'generate']);
    Route::post('/keys/rotate',   [UserKeyController::class, 'rotate']);

    // Files
    Route::get('/files',                 [FileController::class, 'index']);
    Route::post('/files',                [FileController::class, 'store']);
    Route::get('/files/{file}',          [FileController::class, 'show']);
    Route::get('/files/{file}/download', [FileController::class, 'download']);
    Route::patch('/files/{file}',        [FileController::class, 'update']);
    Route::delete('/files/{file}',       [FileController::class, 'destroy']);

    // File Sharing
    Route::get('/files/{file}/share',                [FileShareController::class, 'index']);
    Route::post('/files/{file}/share',               [FileShareController::class, 'store']);
    Route::delete('/files/{file}/share/{share}',     [FileShareController::class, 'destroy']);

    // Search
    Route::get('/search', [SearchController::class, 'index']);
});

// RBAC-Specific Routes
Route::get('/admin-only', [AdminController::class,'index'])
    ->middleware(['auth:api','role:admin']);

Route::get('/staff-area', [StaffController::class,'index'])
    ->middleware(['auth:api','role:staff|admin']);

