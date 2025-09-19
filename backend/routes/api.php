<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StaffController;

//Test Route
Route::get('/hello', function () {
    return response()->json(['message' => 'Hello from Laravel!']);
});


// Authentication Routes
Route::post('register', [AuthController::class,'register']);
Route::post('login', [AuthController::class,'login'])->middleware('throttle:10,1'); // 10 attempts per minute

Route::group(['middleware' => ['auth:api']], function() {
    Route::post('logout', [AuthController::class,'logout']);
    Route::get('me', [AuthController::class,'me']);
    Route::post('refresh', [AuthController::class,'refresh']);
});


Route::get('/admin-only', [AdminController::class,'index'])->middleware(['auth:api','role:admin']);
Route::get('/staff-area', [StaffController::class,'index'])->middleware(['auth:api','role:staff|admin']);

