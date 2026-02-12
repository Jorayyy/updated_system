<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PayrollPeriod;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index()
    {
        $payrolls = PayrollPeriod::orderBy('start_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $payrolls
        ]);
    }
}
