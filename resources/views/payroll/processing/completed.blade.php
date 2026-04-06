@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Accounting: Phase 3 - Generation Completed') }}
        </h2>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 text-center">
        <!-- Progress Stepper -->
        <div class="flex items-center justify-between mb-12 space-x-2 text-sm text-green-600 font-medium font-bold uppercase tracking-widest">
            <div class="flex-1 flex items-center justify-center p-3 bg-green-50 border border-green-200 rounded-lg shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                Selection
            </div>
            <div class="w-10 h-px bg-green-300"></div>
            <div class="flex-1 flex items-center justify-center p-3 bg-green-50 border border-green-200 rounded-lg shadow-sm">
                 <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                 Verification
            </div>
             <div class="w-10 h-px bg-green-300"></div>
            <div class="flex-1 flex items-center justify-center p-3 text-indigo-600 bg-white border border-indigo-600 rounded-lg shadow-sm">
                 <span class="mr-2 px-2 py-1 bg-indigo-600 text-white rounded-full text-xs">3</span>
                 Generation
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-12">
            <div class="mb-6">
                <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-green-100 mb-4">
                    <svg class="h-12 w-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2 uppercase">Payslips Generated Successfully!</h3>
                <p class="text-gray-500 italic">Phase 3: Automated synchronization to payslips is complete.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10 max-w-4xl mx-auto">
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Processed</div>
                    <div class="text-3xl font-black text-indigo-600">{{ $results['computed'] ?? 0 }}</div>
                    <div class="text-[10px] text-gray-500 mt-1">Total Employees Generated</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Status</div>
                    <div class="text-3xl font-black text-green-600 uppercase">DRAFT</div>
                    <div class="text-[10px] text-gray-500 mt-1">Ready for HR Approval</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Period</div>
                    <div class="text-sm font-bold text-gray-700 mt-2">{{ $period->start_date->format('M d') }} - {{ $period->end_date->format('d, Y') }}</div>
                    <div class="text-[10px] text-gray-500 mt-1">{{ $period->payrollGroup->name }}</div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                <a href="{{ route('payroll-periods.show', $period->id) }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-bold rounded-md hover:bg-indigo-700 transition shadow-lg uppercase text-xs tracking-widest">
                    View Period Details & Payslips
                </a>
                <a href="{{ route('payroll.processing.index') }}" class="inline-flex items-center px-6 py-3 bg-white border border-gray-300 text-gray-700 font-bold rounded-md hover:bg-gray-50 transition uppercase text-xs tracking-widest">
                    Process Another Group
                </a>
            </div>

            @if(!empty($results['errors']))
                <div class="mt-10 p-4 bg-red-50 border border-red-200 rounded-lg text-left max-w-lg mx-auto">
                    <h4 class="text-red-800 font-bold text-sm mb-2 flex items-center">
                        <svg class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zm-1 9a1 1 0 01-1-1v-4a1 1 0 112 0v4a1 1 0 01-1 1z" clip-rule="evenodd"></path></svg>
                        Skipped Records:
                    </h4>
                    <ul class="text-xs text-red-700 space-y-1">
                        @foreach($results['errors'] as $userId => $error)
                            <li>&bull; <span class="font-bold">ID {{ $userId }}:</span> {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
