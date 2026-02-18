<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\DTRController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PayrollComputationController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ComputerController;
use App\Http\Controllers\LeaveCreditsController;
use App\Http\Controllers\ConcernController;
use App\Http\Controllers\AllowedIpController;
use App\Http\Controllers\TimekeepingController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\DtrApprovalController;
use App\Http\Controllers\AutomationController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\PayrollGroupController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\OvertimeRequestController;
use App\Http\Controllers\OfficialBusinessController;
use App\Http\Controllers\EmployeeDocumentController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ExpenseClaimController;
use App\Http\Controllers\PerformanceReviewController;
use App\Http\Controllers\CompanyAssetController;
use App\Http\Controllers\ShiftChangeRequestController;
use App\Http\Controllers\HrPolicyController;
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
    // NOTIFICATIONS (All authenticated users)
    // ============================================
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    Route::match(['get', 'post'], '/notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications/delete-all-read', [NotificationController::class, 'deleteAllRead'])->name('notifications.delete-all-read');

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

    // DTR Records - Employee (New Workflow)
    Route::get('/my-dtr-records', [DtrApprovalController::class, 'index'])->name('dtr-records.index');
    Route::get('/my-dtr-records/{dailyTimeRecord}', [DtrApprovalController::class, 'show'])->name('dtr-records.show');
    Route::get('/my-dtr-records/{dailyTimeRecord}/request-correction', [DtrApprovalController::class, 'showCorrectionForm'])->name('dtr-records.correction-form');
    Route::post('/my-dtr-records/{dailyTimeRecord}/request-correction', [DtrApprovalController::class, 'requestCorrection'])->name('dtr-records.request-correction');
    Route::get('/my-dtr-summary', [DtrApprovalController::class, 'summary'])->name('dtr-records.summary');

    // Leaves - Employee
    Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves.index');
    Route::get('/leaves/create', [LeaveController::class, 'create'])->name('leaves.create');
    Route::post('/leaves', [LeaveController::class, 'store'])->name('leaves.store');
    Route::get('/leaves/{leave}', [LeaveController::class, 'show'])->name('leaves.show');
    Route::patch('/leaves/{leave}/cancel', [LeaveController::class, 'cancel'])->name('leaves.cancel');

    // Payslips - Employee (Enhanced)
    Route::prefix('payslip')->name('payslip.')->group(function () {
        Route::get('/', [PayslipController::class, 'index'])->name('index');
        Route::get('/{payroll}', [PayslipController::class, 'show'])->name('show');
        Route::get('/{payroll}/download', [PayslipController::class, 'download'])->name('download');
        Route::get('/{payroll}/view', [PayslipController::class, 'view'])->name('view');
        Route::get('/ytd-summary', [PayslipController::class, 'ytdSummary'])->name('ytd-summary');
    });

    // Payslips - Employee (Legacy routes for backward compatibility)
    Route::get('/my-payslips', [PayrollController::class, 'myPayslips'])->name('payroll.my-payslips');
    Route::get('/my-payslip/{payroll}', [PayrollController::class, 'myPayslip'])->name('payroll.my-payslip');
    Route::get('/my-payslip/{payroll}/pdf', [PayrollController::class, 'myPayslipPdf'])->name('payroll.my-payslip-pdf');

    // PC Selection - Employee
    Route::get('/my-pc', [ComputerController::class, 'selectView'])->name('computers.my-pc');
    Route::post('/my-pc/select', [ComputerController::class, 'select'])->name('computers.select');
    Route::post('/my-pc/release', [ComputerController::class, 'release'])->name('computers.release');

    // Timekeeping - Employee
    Route::get('/timekeeping', [TimekeepingController::class, 'index'])->name('timekeeping.index');
    Route::post('/timekeeping', [TimekeepingController::class, 'store'])->name('timekeeping.store');
    Route::get('/timekeeping/{transaction}', [TimekeepingController::class, 'show'])->name('timekeeping.show');

    // Concerns - Employee
    Route::get('/my-concerns', [ConcernController::class, 'myConcerns'])->name('concerns.my');
    Route::get('/my-concerns/create', [ConcernController::class, 'userCreate'])->name('concerns.user-create');
    Route::post('/my-concerns', [ConcernController::class, 'userStore'])->name('concerns.user-store');
    Route::get('/my-concerns/{concern}', [ConcernController::class, 'userShow'])->name('concerns.user-show');
    Route::post('/my-concerns/{concern}/comment', [ConcernController::class, 'userComment'])->name('concerns.user-comment');

    // ============================================
    // TRANSACTIONS - Employee (All authenticated users)
    // ============================================
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/history', [TransactionController::class, 'history'])->name('transactions.history');
    Route::get('/transactions/create/{type}', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions/{type}', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::patch('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');

    // ============================================
    // HR & ADMIN ROUTES
    // ============================================
    Route::middleware('hr')->group(function () {
        // Sites & Accounts Management
        Route::resource('sites', SiteController::class);
        Route::resource('accounts', AccountController::class);
        Route::resource('schedules', ScheduleController::class);
        
        Route::post('/payroll-groups/{payrollGroup}/add-employee', [PayrollGroupController::class, 'addEmployee'])->name('payroll-groups.add-employee');
        Route::delete('/payroll-groups/{payrollGroup}/remove-employee/{user}', [PayrollGroupController::class, 'removeEmployee'])->name('payroll-groups.remove-employee');
        Route::resource('payroll-groups', PayrollGroupController::class);

        // Employees Management
        Route::resource('employees', EmployeeController::class);
        Route::post('/employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');
        Route::post('/employees/{employee}/force-delete', [EmployeeController::class, 'forceDelete'])->name('employees.force-delete');
        Route::post('/employees/bulk-assign-site', [EmployeeController::class, 'bulkAssignSite'])->name('employees.bulk-assign-site');
        Route::post('/employees/bulk-assign-account', [EmployeeController::class, 'bulkAssignAccount'])->name('employees.bulk-assign-account');

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
        
        // Approval routes restricted to Super Admin
        Route::middleware('role:super_admin')->group(function () {
            Route::patch('/leaves/{leave}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
            Route::patch('/leaves/{leave}/hr-approve', [LeaveController::class, 'hrApprove'])->name('leaves.hr-approve');
            Route::patch('/leaves/{leave}/admin-approve', [LeaveController::class, 'adminApprove'])->name('leaves.admin-approve');
            Route::patch('/leaves/{leave}/reject', [LeaveController::class, 'reject'])->name('leaves.reject');
            Route::patch('/leaves/{leave}/admin-cancel', [LeaveController::class, 'adminCancel'])->name('leaves.admin-cancel');
        });

        // Leave Types Management (HR can still manage types)
        Route::resource('leave-types', LeaveTypeController::class);

        // Leave Credits Management (SuperAdmin Only)
        Route::middleware('role:super_admin')->group(function () {
            Route::get('/leave-credits', [LeaveCreditsController::class, 'index'])->name('leave-credits.index');
            Route::get('/leave-credits/{employee}/edit', [LeaveCreditsController::class, 'edit'])->name('leave-credits.edit');
            Route::put('/leave-credits/{employee}', [LeaveCreditsController::class, 'update'])->name('leave-credits.update');
            Route::post('/leave-credits/bulk-allocate', [LeaveCreditsController::class, 'bulkAllocate'])->name('leave-credits.bulk-allocate');
            Route::post('/leave-credits/carry-over', [LeaveCreditsController::class, 'carryOver'])->name('leave-credits.carry-over');
            Route::post('/leave-credits/{employee}/adjust', [LeaveCreditsController::class, 'adjust'])->name('leave-credits.adjust');
            Route::get('/leave-credits/{employee}/history', [LeaveCreditsController::class, 'history'])->name('leave-credits.history');
        });

        // DTR Management
        Route::get('/manage/dtr', [DTRController::class, 'adminIndex'])->name('dtr.admin-index');
        Route::get('/manage/dtr/{user}', [DTRController::class, 'show'])->name('dtr.show');
        Route::get('/manage/dtr/{user}/pdf', [DTRController::class, 'employeePdf'])->name('dtr.employee-pdf');
        Route::post('/manage/dtr/bulk-pdf', [DTRController::class, 'bulkPdf'])->name('dtr.bulk-pdf');

        // DTR Approval Workflow (New)
        Route::prefix('dtr-approval')->name('dtr-approval.')->group(function () {
            Route::get('/', [DtrApprovalController::class, 'index'])->name('index');
            Route::get('/pending', [DtrApprovalController::class, 'pendingApprovals'])->name('pending');
            Route::get('/corrections', [DtrApprovalController::class, 'correctionRequests'])->name('corrections');
            Route::get('/{dailyTimeRecord}', [DtrApprovalController::class, 'show'])->name('show');
            Route::get('/{dailyTimeRecord}/edit', [DtrApprovalController::class, 'edit'])->name('edit');
            Route::put('/{dailyTimeRecord}', [DtrApprovalController::class, 'update'])->name('update');
            Route::post('/{dailyTimeRecord}/approve', [DtrApprovalController::class, 'approve'])->name('approve');
            Route::post('/{dailyTimeRecord}/reject', [DtrApprovalController::class, 'reject'])->name('reject');
            Route::post('/{dailyTimeRecord}/approve-correction', [DtrApprovalController::class, 'approveCorrection'])->name('approve-correction');
            Route::post('/{dailyTimeRecord}/reject-correction', [DtrApprovalController::class, 'rejectCorrection'])->name('reject-correction');
            Route::post('/bulk-approve', [DtrApprovalController::class, 'bulkApprove'])->name('bulk-approve');
            Route::post('/bulk-approve-corrections', [DtrApprovalController::class, 'bulkApproveCorrections'])->name('bulk-approve-corrections');
            Route::post('/period/{payrollPeriod}/approve-all', [DtrApprovalController::class, 'approveAllForPeriod'])->name('approve-all-period');
            Route::post('/generate', [DtrApprovalController::class, 'generateDtrs'])->name('generate');
        });

        // Payroll Management
        Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
        Route::get('/payroll/periods', [PayrollController::class, 'periods'])->name('payroll.periods');
        
        // Modification routes restricted to HR Accounting
        Route::middleware('role:accounting,super_admin')->group(function () {
            Route::get('/payroll/periods/create', [PayrollController::class, 'createPeriod'])->name('payroll.create-period');
            Route::post('/payroll/periods', [PayrollController::class, 'storePeriod'])->name('payroll.store-period');
            Route::post('/payroll/periods/{period}/process', [PayrollController::class, 'processPeriod'])->name('payroll.process-period');
            Route::post('/payroll/periods/{period}/complete', [PayrollController::class, 'completePeriod'])->name('payroll.complete-period');
            Route::post('/payroll/periods/{period}/recompute/{user}', [PayrollController::class, 'recompute'])->name('payroll.recompute');
            Route::post('/payroll/periods/{period}/bulk-release', [PayrollController::class, 'bulkRelease'])->name('payroll.bulk-release');
            Route::post('/payroll/{payroll}/release', [PayrollController::class, 'release'])->name('payroll.release');
            Route::delete('/payroll/periods/{period}', [PayrollController::class, 'destroyPeriod'])->name('payroll.destroy-period');
        });

        Route::get('/payroll/periods/{period}', [PayrollController::class, 'showPeriod'])->name('payroll.show-period');
        Route::get('/payroll/periods/{period}/report', [PayrollController::class, 'report'])->name('payroll.report');
        Route::get('/payroll/periods/{period}/generate-report', [PayrollController::class, 'generateReport'])->name('payroll.generate-report');
        Route::get('/payroll/{payroll}/payslip', [PayrollController::class, 'payslip'])->name('payroll.payslip');
        Route::get('/payroll/{payroll}/payslip-pdf', [PayrollController::class, 'payslipPdf'])->name('payroll.payslip-pdf');

        // Payroll Computation (DTR-Based Workflow) - Restricted to HR Accounting
        Route::prefix('payroll/computation')->name('payroll.computation.')->middleware('role:accounting,super_admin')->group(function () {
            // New 3-Phase Wizard - REMOVED PER USER REQUEST
            // Route::get('/wizard/{period}', [App\Http\Controllers\PayrollWizardController::class, 'show'])->name('wizard');
            
            // Progress API
            Route::get('/period/{period}/progress', [PayrollComputationController::class, 'progress'])->name('progress');
            Route::post('/period/{period}/reset', [PayrollComputationController::class, 'resetProcessing'])->name('reset');

            Route::get('/', [PayrollComputationController::class, 'dashboard'])->name('dashboard');
            Route::get('/period/{period}/preview', [PayrollComputationController::class, 'preview'])->name('preview');
            Route::post('/period/{period}/compute', [PayrollComputationController::class, 'compute'])->name('compute');
            Route::get('/period/{period}', [PayrollComputationController::class, 'show'])->name('show');
            Route::get('/period/{period}/status', [PayrollComputationController::class, 'status'])->name('status');
            Route::get('/period/{period}/export', [PayrollComputationController::class, 'export'])->name('export');
            Route::post('/period/{period}/generate-dtrs', [PayrollComputationController::class, 'generateDtrs'])->name('generate-dtrs');
            
            Route::get('/{payroll}/details', [PayrollComputationController::class, 'details'])->name('details');
            Route::get('/{payroll}/edit', [PayrollComputationController::class, 'edit'])->name('edit');
            Route::put('/{payroll}', [PayrollComputationController::class, 'update'])->name('update');
            Route::post('/{payroll}/recompute', [PayrollComputationController::class, 'recompute'])->name('recompute');
            Route::post('/{payroll}/approve', [PayrollComputationController::class, 'approve'])->name('approve');
            Route::post('/{payroll}/release', [PayrollComputationController::class, 'release'])->name('release');
            Route::post('/{payroll}/reject', [PayrollComputationController::class, 'reject'])->name('reject');
            Route::post('/{payroll}/post', [PayrollComputationController::class, 'post'])->name('post');
            
            Route::post('/period/{period}/bulk-approve', [PayrollComputationController::class, 'bulkApprove'])->name('bulk-approve');
            Route::post('/period/{period}/bulk-release', [PayrollComputationController::class, 'bulkRelease'])->name('bulk-release');
            Route::post('/period/{period}/bulk-post', [PayrollComputationController::class, 'bulkPost'])->name('bulk-post');
            Route::delete('/period/{period}/bulk-delete', [PayrollComputationController::class, 'bulkDelete'])->name('bulk-delete');
            Route::delete('/{payroll}', [PayrollComputationController::class, 'destroy'])->name('destroy');
        });

        // Payslip Management (Admin/HR)
        Route::prefix('payslip-admin')->name('payslip.admin.')->group(function () {
            Route::get('/{payroll}', [PayslipController::class, 'adminShow'])->name('show');
            Route::get('/{payroll}/download', [PayslipController::class, 'adminDownload'])->name('download');
            Route::post('/{payroll}/send-email', [PayslipController::class, 'sendEmail'])->name('send-email');
            Route::post('/{payroll}/resend-email', [PayslipController::class, 'resendEmail'])->name('resend-email');
            Route::post('/period/{period}/bulk-generate', [PayslipController::class, 'bulkGenerate'])->name('bulk-generate');
            Route::get('/period/{period}/bulk-download', [PayslipController::class, 'bulkDownload'])->name('bulk-download');
            Route::post('/period/{period}/bulk-send-email', [PayslipController::class, 'bulkSendEmail'])->name('bulk-send-email');
            Route::get('/period/{period}/distribution-status', [PayslipController::class, 'distributionStatus'])->name('distribution-status');
        });

        // Departments Management
        Route::resource('departments', DepartmentController::class);

        // Automation Dashboard
        Route::prefix('automation')->name('automation.')->group(function () {
            Route::get('/', [AutomationController::class, 'index'])->name('index');
            Route::post('/generate-dtrs', [AutomationController::class, 'generateDtrs'])->name('generate-dtrs');
            Route::post('/compute-payroll', [AutomationController::class, 'computePayroll'])->name('compute-payroll');
            Route::post('/retry-failed-jobs', [AutomationController::class, 'retryFailedJobs'])->name('retry-failed-jobs');
        });

        // PC Management
        Route::get('/computers', [ComputerController::class, 'index'])->name('computers.index');
        Route::get('/computers/create', [ComputerController::class, 'create'])->name('computers.create');
        Route::post('/computers', [ComputerController::class, 'store'])->name('computers.store');
        Route::get('/computers/{computer}', [ComputerController::class, 'show'])->name('computers.show');
        Route::get('/computers/{computer}/edit', [ComputerController::class, 'edit'])->name('computers.edit');
        Route::put('/computers/{computer}', [ComputerController::class, 'update'])->name('computers.update');
        Route::delete('/computers/{computer}', [ComputerController::class, 'destroy'])->name('computers.destroy');
        Route::post('/computers/{computer}/assign', [ComputerController::class, 'assignToUser'])->name('computers.assign');
        Route::post('/computers/{computer}/release', [ComputerController::class, 'adminRelease'])->name('computers.admin-release');
        Route::get('/computers/active-usage', [ComputerController::class, 'activeUsage'])->name('computers.active-usage');

        // Transactions Management (HR/Admin)
        Route::get('/manage/transactions', [TransactionController::class, 'adminIndex'])->name('transactions.admin-index');
        Route::patch('/transactions/{transaction}/hr-approve', [TransactionController::class, 'hrApprove'])->name('transactions.hr-approve');
        Route::patch('/transactions/{transaction}/admin-approve', [TransactionController::class, 'adminApprove'])->name('transactions.admin-approve');
        Route::patch('/transactions/{transaction}/reject', [TransactionController::class, 'reject'])->name('transactions.reject');

        // Analytics Dashboard (HR/Admin)
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/analytics/attendance', [AnalyticsController::class, 'attendanceData'])->name('analytics.attendance');
        Route::get('/analytics/turnover', [AnalyticsController::class, 'turnoverData'])->name('analytics.turnover');
        Route::get('/analytics/leaves', [AnalyticsController::class, 'leaveData'])->name('analytics.leaves');
        Route::get('/analytics/payroll', [AnalyticsController::class, 'payrollData'])->name('analytics.payroll');
    });

    // ============================================
    // NEW FEATURES
    // ============================================

    // Overtime Requests
    Route::resource('overtime-requests', OvertimeRequestController::class);
    Route::patch('/overtime-requests/{overtime_request}/approve', [OvertimeRequestController::class, 'approve'])->name('overtime-requests.approve');
    Route::patch('/overtime-requests/{overtime_request}/reject', [OvertimeRequestController::class, 'reject'])->name('overtime-requests.reject');

    // Official Business
    Route::resource('official-businesses', OfficialBusinessController::class);
    Route::patch('/official-businesses/{official_business}/approve', [OfficialBusinessController::class, 'approve'])->name('official-businesses.approve');
    Route::patch('/official-businesses/{official_business}/reject', [OfficialBusinessController::class, 'reject'])->name('official-businesses.reject');

    // Employee Documents (201 File)
    Route::resource('employee-documents', EmployeeDocumentController::class);
    Route::get('/employee-documents/{employee_document}/download', [EmployeeDocumentController::class, 'download'])->name('employee-documents.download');

    // Announcements
    Route::resource('announcements', AnnouncementController::class);
    Route::patch('/announcements/{announcement}/pin', [AnnouncementController::class, 'togglePin'])->name('announcements.pin');

    // Expense Claims
    Route::resource('expense-claims', ExpenseClaimController::class);
    Route::patch('/expense-claims/{expense_claim}/approve', [ExpenseClaimController::class, 'approve'])->name('expense-claims.approve');
    Route::patch('/expense-claims/{expense_claim}/reject', [ExpenseClaimController::class, 'reject'])->name('expense-claims.reject');

    // Performance Reviews
    Route::resource('performance-reviews', PerformanceReviewController::class);
    Route::patch('/performance-reviews/{performance_review}/acknowledge', [PerformanceReviewController::class, 'acknowledge'])->name('performance-reviews.acknowledge');

    // Company Assets
    Route::resource('company-assets', CompanyAssetController::class);

    // Shift Change Requests
    Route::resource('shift-change-requests', ShiftChangeRequestController::class);
    Route::patch('/shift-change-requests/{shift_change_request}/approve', [ShiftChangeRequestController::class, 'approve'])->name('shift-change-requests.approve');
    Route::patch('/shift-change-requests/{shift_change_request}/reject', [ShiftChangeRequestController::class, 'reject'])->name('shift-change-requests.reject');

    // HR Policies
    Route::resource('hr-policies', HrPolicyController::class);

    // ============================================
    // SUPER ADMIN ONLY ROUTES
    // ============================================
    Route::middleware('role:super_admin')->group(function () {
        // Holiday Management
        Route::resource('holidays', HolidayController::class);
        Route::post('/holidays/generate-recurring', [HolidayController::class, 'generateRecurring'])->name('holidays.generate-recurring');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
        Route::get('/reports/attendance/export', [ReportController::class, 'exportAttendanceCsv'])->name('reports.attendance.export');
        Route::get('/reports/leaves', [ReportController::class, 'leaves'])->name('reports.leaves');
        Route::get('/reports/leaves/export', [ReportController::class, 'exportLeavesCsv'])->name('reports.leaves.export');
        Route::get('/reports/payroll', [ReportController::class, 'payroll'])->name('reports.payroll');
        Route::get('/reports/payroll/export', [ReportController::class, 'exportPayrollCsv'])->name('reports.payroll.export');
        Route::get('/reports/employees', [ReportController::class, 'employees'])->name('reports.employees');
        Route::get('/reports/employees/export', [ReportController::class, 'exportEmployeesCsv'])->name('reports.employees.export');

        // Audit Logs
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');

        // Timekeeping Management
        Route::get('/manage/timekeeping', [TimekeepingController::class, 'adminIndex'])->name('timekeeping.admin-index');
        Route::post('/manage/timekeeping', [TimekeepingController::class, 'adminStore'])->name('timekeeping.admin-store');
        Route::patch('/timekeeping/{transaction}/void', [TimekeepingController::class, 'void'])->name('timekeeping.void');
        Route::get('/timekeeping/live-stats', [TimekeepingController::class, 'liveStats'])->name('timekeeping.live-stats');
        // Settings Management
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::get('/settings/company', [SettingsController::class, 'company'])->name('settings.company');
        Route::put('/settings/company', [SettingsController::class, 'updateCompany'])->name('settings.company.update');
        Route::get('/settings/payroll', [SettingsController::class, 'payroll'])->name('settings.payroll');
        Route::put('/settings/payroll', [SettingsController::class, 'updatePayroll'])->name('settings.payroll.update');
        Route::post('/settings/payroll/adjustment-types', [SettingsController::class, 'addAdjustmentType'])->name('settings.payroll.adjustment-types.store');
        Route::delete('/settings/payroll/adjustment-types/{type}', [SettingsController::class, 'deleteAdjustmentType'])->name('settings.payroll.adjustment-types.destroy');
        Route::get('/settings/attendance', [SettingsController::class, 'attendance'])->name('settings.attendance');
        Route::put('/settings/attendance', [SettingsController::class, 'updateAttendance'])->name('settings.attendance.update');
        Route::get('/settings/call-center', [SettingsController::class, 'callCenter'])->name('settings.call-center');
        Route::put('/settings/call-center', [SettingsController::class, 'updateCallCenter'])->name('settings.call-center.update');
        Route::get('/settings/leave', [SettingsController::class, 'leave'])->name('settings.leave');
        Route::put('/settings/leave', [SettingsController::class, 'updateLeave'])->name('settings.leave.update');
        Route::get('/settings/notifications', [SettingsController::class, 'notifications'])->name('settings.notifications');
        Route::put('/settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications.update');
        Route::get('/settings/system', [SettingsController::class, 'system'])->name('settings.system');
        Route::put('/settings/system', [SettingsController::class, 'updateSystem'])->name('settings.system.update');

        // Allowed IPs Management
        Route::get('/settings/allowed-ips', [AllowedIpController::class, 'index'])->name('settings.allowed-ips');
        Route::post('/settings/allowed-ips', [AllowedIpController::class, 'store'])->name('settings.allowed-ips.store');
        Route::put('/settings/allowed-ips/{allowedIp}', [AllowedIpController::class, 'update'])->name('settings.allowed-ips.update');
        Route::patch('/settings/allowed-ips/{allowedIp}/toggle', [AllowedIpController::class, 'toggle'])->name('settings.allowed-ips.toggle');
        Route::delete('/settings/allowed-ips/{allowedIp}', [AllowedIpController::class, 'destroy'])->name('settings.allowed-ips.destroy');
        Route::post('/settings/allowed-ips/add-current', [AllowedIpController::class, 'addCurrentIp'])->name('settings.allowed-ips.add-current');

        // Backup Management
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [BackupController::class, 'create'])->name('backups.create');
        Route::get('/backups/{filename}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::post('/backups/{filename}/restore', [BackupController::class, 'restore'])->name('backups.restore');
        Route::post('/backups/upload', [BackupController::class, 'upload'])->name('backups.upload');
        Route::delete('/backups/{filename}', [BackupController::class, 'destroy'])->name('backups.destroy');

        // Concerns/Tickets Management
        Route::get('/concerns', [ConcernController::class, 'index'])->name('concerns.index');
        Route::get('/concerns/create', [ConcernController::class, 'create'])->name('concerns.create');
        Route::post('/concerns', [ConcernController::class, 'store'])->name('concerns.store');
        Route::get('/concerns/{concern}', [ConcernController::class, 'show'])->name('concerns.show');
        Route::get('/concerns/{concern}/edit', [ConcernController::class, 'edit'])->name('concerns.edit');
        Route::put('/concerns/{concern}', [ConcernController::class, 'update'])->name('concerns.update');
        Route::delete('/concerns/{concern}', [ConcernController::class, 'destroy'])->name('concerns.destroy');
        Route::patch('/concerns/{concern}/status', [ConcernController::class, 'updateStatus'])->name('concerns.status');
        Route::patch('/concerns/{concern}/assign', [ConcernController::class, 'assign'])->name('concerns.assign');
        Route::patch('/concerns/{concern}/priority', [ConcernController::class, 'updatePriority'])->name('concerns.priority');
        Route::post('/concerns/{concern}/comment', [ConcernController::class, 'addComment'])->name('concerns.comment');
        Route::get('/concerns-stats', [ConcernController::class, 'stats'])->name('concerns.stats');
    });
});

require __DIR__.'/auth.php';
