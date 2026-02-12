<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public read-only APIs (or protect with auth:sanctum if needed)
Route::group(['namespace' => 'App\Http\Controllers\Api'], function () {
    Route::get('/employees', [App\Http\Controllers\Api\EmployeeController::class, 'index']);
    Route::get('/employees/{id}', [App\Http\Controllers\Api\EmployeeController::class, 'show']);
    
    Route::get('/attendance', [App\Http\Controllers\Api\AttendanceController::class, 'index']);
    
    Route::get('/leaves', [App\Http\Controllers\Api\LeaveController::class, 'index']);
    
    Route::get('/payroll', [App\Http\Controllers\Api\PayrollController::class, 'index']);
    
    Route::get('/payslips', [App\Http\Controllers\Api\PayslipController::class, 'index']);
});

