<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use App\Models\DailyTimeRecord;
use App\Models\Payroll;
use App\Services\PayrollComputationService;
use App\Services\DtrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollWizardController extends Controller
{
    protected PayrollComputationService $computationService;
    protected DtrService $dtrService;

    public function __construct(PayrollComputationService $computationService, DtrService $dtrService)
    {
        $this->computationService = $computationService;
        $this->dtrService = $dtrService;
    }

    public function show(PayrollPeriod $period)
    {
        // 1. DTR Status
        $dtrStats = [
            'total' => DailyTimeRecord::where('payroll_period_id', $period->id)->count(),
            'approved' => DailyTimeRecord::where('payroll_period_id', $period->id)->where('status', 'approved')->count(),
            'pending' => DailyTimeRecord::where('payroll_period_id', $period->id)->where('status', 'pending')->count(),
            'correction' => DailyTimeRecord::where('payroll_period_id', $period->id)->where('status', 'correction_requested')->count(),
            'rejected' => DailyTimeRecord::where('payroll_period_id', $period->id)->where('status', 'rejected')->count(),
        ];
        
        $dtrsGenerated = $dtrStats['total'] > 0;
        $allDtrsApproved = $dtrsGenerated && ($dtrStats['total'] == $dtrStats['approved']);

        // 2. Payroll Status
        $payrollStats = [
            'total' => Payroll::where('payroll_period_id', $period->id)->count(),
            'approved' => Payroll::where('payroll_period_id', $period->id)->where('status', 'approved')->count(),
            'completed' => Payroll::where('payroll_period_id', $period->id)->whereIn('status', ['completed', 'released'])->count(),
        ];

        $payrollComputed = $payrollStats['total'] > 0;
        $allPayrollApproved = $payrollComputed && ($payrollStats['total'] == $payrollStats['approved'] + $payrollStats['completed']);
        
        $payrollSummary = null;
        if ($payrollComputed) {
            $payrollSummary = [
                'total_gross' => Payroll::where('payroll_period_id', $period->id)->sum('gross_pay'),
                'total_net' => Payroll::where('payroll_period_id', $period->id)->sum('net_pay'),
                'count' => $payrollStats['total']
            ];
        }

        // 3. Payslip/Posting Status
        $postingStats = [
            'posted' => Payroll::where('payroll_period_id', $period->id)->where('is_posted', true)->count(),
            'released' => Payroll::where('payroll_period_id', $period->id)->where('status', 'released')->count(),
        ];

        return view('payroll.wizard.show', compact(
            'period', 
            'dtrStats', 
            'dtrsGenerated', 
            'allDtrsApproved',
            'payrollStats',
            'payrollComputed',
            'allPayrollApproved',
            'payrollSummary',
            'postingStats'
        ));
    }
}
