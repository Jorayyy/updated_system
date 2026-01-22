<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\DTRController;
use App\Http\Controllers\PayrollController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ============================================
    // EMPLOYEE ROUTES (All authenticated users)
    // ============================================
    
    // Attendance - Employee actions (Sequential Steps)
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/step', [AttendanceController::class, 'processStep'])->name('attendance.step');
    Route::post('/attendance/time-in', [AttendanceController::class, 'timeIn'])->name('attendance.time-in');
    Route::post('/attendance/time-out', [AttendanceController::class, 'timeOut'])->name('attendance.time-out');
    Route::post('/attendance/break/start', [AttendanceController::class, 'startBreak'])->name('attendance.break.start');
    Route::post('/attendance/break/end', [AttendanceController::class, 'endBreak'])->name('attendance.break.end');
    Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');

    // DTR - Employee
    Route::get('/dtr', [DTRController::class, 'index'])->name('dtr.index');
    Route::get('/dtr/pdf', [DTRController::class, 'generatePdf'])->name('dtr.pdf');

    // Leaves - Employee
    Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves.index');
    Route::get('/leaves/create', [LeaveController::class, 'create'])->name('leaves.create');
    Route::post('/leaves', [LeaveController::class, 'store'])->name('leaves.store');
    Route::get('/leaves/{leave}', [LeaveController::class, 'show'])->name('leaves.show');
    Route::patch('/leaves/{leave}/cancel', [LeaveController::class, 'cancel'])->name('leaves.cancel');

    // Payslips - Employee
    Route::get('/my-payslips', [PayrollController::class, 'myPayslips'])->name('payroll.my-payslips');
    Route::get('/my-payslip/{payroll}', [PayrollController::class, 'myPayslip'])->name('payroll.my-payslip');
    Route::get('/my-payslip/{payroll}/pdf', [PayrollController::class, 'myPayslipPdf'])->name('payroll.my-payslip-pdf');

    // ============================================
    // HR & ADMIN ROUTES
    // ============================================
    Route::middleware('hr')->group(function () {
        // Employees Management
        Route::resource('employees', EmployeeController::class);
        Route::post('/employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');

        // Attendance Management
        Route::get('/manage/attendance', [AttendanceController::class, 'manage'])->name('attendance.manage');
        Route::get('/manage/attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
        Route::post('/manage/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('/manage/attendance/{attendance}', [AttendanceController::class, 'show'])->name('attendance.show');
        Route::get('/manage/attendance/{attendance}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
        Route::put('/manage/attendance/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');

        // Leave Management
        Route::get('/manage/leaves', [LeaveController::class, 'manage'])->name('leaves.manage');
        Route::get('/manage/leaves/{leave}', [LeaveController::class, 'adminShow'])->name('leaves.admin-show');
        Route::patch('/leaves/{leave}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
        Route::patch('/leaves/{leave}/reject', [LeaveController::class, 'reject'])->name('leaves.reject');

        // Leave Types Management
        Route::resource('leave-types', LeaveTypeController::class);

        // DTR Management
        Route::get('/manage/dtr', [DTRController::class, 'adminIndex'])->name('dtr.admin-index');
        Route::get('/manage/dtr/{user}', [DTRController::class, 'show'])->name('dtr.show');
        Route::get('/manage/dtr/{user}/pdf', [DTRController::class, 'employeePdf'])->name('dtr.employee-pdf');
        Route::post('/manage/dtr/bulk-pdf', [DTRController::class, 'bulkPdf'])->name('dtr.bulk-pdf');

        // Payroll Management
        Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
        Route::get('/payroll/periods', [PayrollController::class, 'periods'])->name('payroll.periods');
        Route::get('/payroll/periods/create', [PayrollController::class, 'createPeriod'])->name('payroll.create-period');
        Route::post('/payroll/periods', [PayrollController::class, 'storePeriod'])->name('payroll.store-period');
        Route::get('/payroll/periods/{period}', [PayrollController::class, 'showPeriod'])->name('payroll.show-period');
        Route::post('/payroll/periods/{period}/process', [PayrollController::class, 'processPeriod'])->name('payroll.process-period');
        Route::get('/payroll/periods/{period}/report', [PayrollController::class, 'report'])->name('payroll.report');
        Route::get('/payroll/periods/{period}/generate-report', [PayrollController::class, 'generateReport'])->name('payroll.generate-report');
        Route::get('/payroll/{payroll}/payslip', [PayrollController::class, 'payslip'])->name('payroll.payslip');
        Route::get('/payroll/{payroll}/payslip-pdf', [PayrollController::class, 'payslipPdf'])->name('payroll.payslip-pdf');
    });
});

require __DIR__.'/auth.php';
