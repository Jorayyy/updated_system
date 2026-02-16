<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payroll Wizard') }} 
            <span class="text-gray-500 font-normal text-lg ml-2">
                {{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }}
                @if($period->payrollGroup)
                    ({{ $period->payrollGroup->name }})
                @endif
            </span>
        </h2>
        <div class="text-sm text-gray-500 mt-1">
            Pay Date: {{ \Carbon\Carbon::parse($period->pay_date)->format('M d, Y') }}
        </div>
    </x-slot>

    <div class="py-12" x-data="{ 
        loading: {{ $period->status === 'processing' ? 'true' : 'false' }}, 
        loadingMessage: '{{ $period->status === 'processing' ? 'Processing in background...' : 'Processing...' }}',
        progress: 0,
        progressTotal: 0,
        progressCurrent: 0,
        polling: false,
        
        init() {
            if (this.loading) {
                this.startPolling();
            }
        },

        startLoading(message) {
            this.loading = true;
            this.loadingMessage = message;
            // Delay start polling slightly to allow request to fly
            setTimeout(() => {
                this.startPolling();
            }, 1000);
        },

        startPolling() {
            this.polling = true;
            let interval = setInterval(() => {
                fetch('{{ route('payroll.computation.progress', $period) }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'processing') {
                            this.progress = data.percentage;
                            this.progressCurrent = data.current;
                            this.progressTotal = data.total;
                            this.loadingMessage = data.message || 'Processing...';
                        } else if (data.status === 'completed') {
                            this.progress = 100;
                            this.loadingMessage = 'Completed! Reloading...';
                            clearInterval(interval);
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            // If status is idle but we are loading, maybe job hasn't started or failed
                            // Keep polling for a bit?
                        }
                    })
                    .catch(error => {
                        console.error('Error polling progress:', error);
                    });
            }, 1500); // Poll every 1.5s
        }
    }">
        {{-- Loading Overlay --}}
        <div x-show="loading" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 transition-opacity" style="display: none;">
            <div class="bg-white p-8 rounded-2xl shadow-2xl text-center max-w-md w-full mx-4 relative overflow-hidden ring-1 ring-gray-900/5">
                
                {{-- Status Icon --}}
                <div class="mb-5 flex justify-center">
                    <div x-show="progress < 100" class="bg-blue-50 p-4 rounded-full">
                        <svg class="w-10 h-10 text-blue-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <div x-show="progress >= 100" class="bg-green-50 p-4 rounded-full" x-cloak>
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>

                <h3 class="text-xl font-bold text-gray-900 mb-1" x-text="loadingMessage">Processing...</h3>
                <p class="text-gray-500 text-sm mb-8" x-text="progressTotal > 0 ? 'Please wait while we process records' : 'Initializing background tasks...'"></p>

                {{-- Big Percentage --}}
                <div class="text-6xl font-black text-gray-900 mb-8 tracking-tighter">
                    <span x-text="Math.round(progress)">0</span><span class="text-4xl text-gray-400">%</span>
                </div>

                {{-- Progress Bar --}}
                <div class="relative w-full bg-gray-100 rounded-full h-3 mb-3 overflow-hidden">
                    <div class="absolute top-0 left-0 h-full bg-gradient-to-r from-blue-500 to-indigo-600 transition-all duration-300 ease-out" 
                         :style="'width: ' + progress + '%'">
                    </div>
                </div>

                {{-- Counter --}}
                <div class="flex justify-between text-xs text-gray-400 font-bold uppercase tracking-wider">
                    <span>Processed</span>
                    <span x-text="progressTotal > 0 ? (progressCurrent + ' / ' + progressTotal) : '-'">-</span>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                    @if(Str::contains(session('error'), 'draft periods'))
                         <div class="mt-2 text-sm text-red-800">
                            <strong>Why is this happening?</strong> The system prevents re-computation if the period is currently "processing" or "completed" to avoid data conflicts. 
                            <br>Please wait for current jobs to finish or check logs if it seems stuck.
                        </div>
                    @endif
                </div>
            @endif

            {{-- Progress Bar --}}
            <div class="mb-8">
                <div class="flex items-center justify-between relative">
                    <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-gray-200 -z-10"></div>
                    
                    {{-- Phase 1 Indicator --}}
                    <div class="flex flex-col items-center bg-white px-4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white mb-2 
                            {{ $allDtrsApproved ? 'bg-green-500' : 'bg-blue-600' }}">
                            @if($allDtrsApproved) ✓ @else 1 @endif
                        </div>
                        <span class="text-sm font-medium {{ $allDtrsApproved ? 'text-green-600' : 'text-blue-600' }}">DTR Collection</span>
                    </div>

                    {{-- Phase 2 Indicator --}}
                    <div class="flex flex-col items-center bg-white px-4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold mb-2 
                            {{ $allPayrollApproved ? 'bg-green-500 text-white' : ($allDtrsApproved ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500') }}">
                            @if($allPayrollApproved) ✓ @else 2 @endif
                        </div>
                        <span class="text-sm font-medium">Payroll Computation</span>
                    </div>

                    {{-- Phase 3 Indicator --}}
                    <div class="flex flex-col items-center bg-white px-4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold mb-2 
                            {{ $postingStats['posted'] > 0 ? 'bg-green-500 text-white' : ($allPayrollApproved ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500') }}">
                            3
                        </div>
                        <span class="text-sm font-medium">Payslip & Finalization</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- PHASE 1: DTR Collection --}}
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border-t-4 {{ $allDtrsApproved ? 'border-green-500' : 'border-blue-500' }}">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Phase 1: DTR</h3>
                            @if($allDtrsApproved)
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">Completed</span>
                            @else
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">In Progress</span>
                            @endif
                        </div>
                        
                        <div class="space-y-4">
                            <div class="text-sm text-gray-600">
                                <p>Generate and approve employee Daily Time Records.</p>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-md">
                                <div class="flex justify-between text-sm mb-1">
                                    <span>Approved:</span>
                                    <span class="font-bold">{{ $dtrStats['approved'] }} / {{ $dtrStats['total'] }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $dtrStats['total'] > 0 ? ($dtrStats['approved'] / $dtrStats['total'] * 100) : 0 }}%"></div>
                                </div>
                                @if($dtrStats['pending'] > 0)
                                    <p class="text-xs text-orange-600 mt-1">{{ $dtrStats['pending'] }} Pending Approval</p>
                                @endif
                                @if($dtrStats['correction'] > 0)
                                    <p class="text-xs text-red-600 mt-1">{{ $dtrStats['correction'] }} Corrections Requested</p>
                                @endif
                            </div>

                            <div class="border-t pt-4 space-y-2">
                                @if(!$dtrsGenerated)
                                    <form action="{{ route('payroll.computation.generate-dtrs', $period) }}" method="POST" @submit="startLoading('Generating DTR Records...')">
                                        @csrf
                                        <button type="submit" class="w-full justify-center inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                            Generate DTRs
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('dtr-approval.index', ['payroll_period_id' => $period->id]) }}" class="w-full block text-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring ring-blue-300 active:text-gray-800 active:bg-gray-50 transition ease-in-out duration-150">
                                        Review & Approve DTRs
                                    </a>
                                    
                                    @if(!$allDtrsApproved)
                                        <form action="{{ route('dtr-approval.approve-all-period', $period) }}" method="POST" onsubmit="return confirm('Are you sure you want to approve ALL pending DTRs for this period?');" @submit="startLoading('Approving DTRs...')">
                                            @csrf
                                            <button type="submit" class="w-full mt-2 justify-center inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                                Bulk Approve All
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PHASE 2: Payroll Computation --}}
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border-t-4 {{ $allPayrollApproved ? 'border-green-500' : ($allDtrsApproved ? 'border-blue-500' : 'border-gray-200 opacity-75') }}">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Phase 2: Payroll</h3>
                            @if(!$allDtrsApproved)
                                <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2.5 py-0.5 rounded">Locked</span>
                            @elseif($allPayrollApproved)
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">Completed</span>
                            @else
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">Ready</span>
                            @endif
                        </div>

                        <div class="space-y-4">
                            <div class="text-sm text-gray-600">
                                <p>Compute gross and net pay based on approved DTRs.</p>
                            </div>

                            @if($payrollComputed)
                                <div class="bg-gray-50 p-4 rounded-md">
                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                        <div class="text-gray-500">Employees:</div>
                                        <div class="font-bold text-right">{{ $payrollStats['total'] }}</div>
                                        
                                        <div class="text-gray-500">Total Net:</div>
                                        <div class="font-bold text-right">₱{{ number_format($payrollSummary['total_net'], 2) }}</div>
                                    </div>
                                    <div class="mt-2 text-xs text-gray-500 text-right">
                                        Approved: {{ $payrollStats['approved'] + $payrollStats['completed'] }} / {{ $payrollStats['total'] }}
                                    </div>
                                </div>
                            @endif

                            <div class="border-t pt-4 space-y-2">
                                @if($allDtrsApproved)
                                    <form action="{{ route('payroll.computation.compute', $period) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full justify-center inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                            {{ $payrollComputed ? 'Re-Compute Payroll' : 'Compute Payroll' }}
                                        </button>
                                    </form>

                                    @if($payrollComputed && !$allPayrollApproved)
                                        <form action="{{ route('payroll.computation.bulk-approve', $period) }}" method="POST" onsubmit="return confirm('Approve all payroll computations?');">
                                            @csrf
                                            <button type="submit" class="w-full mt-2 justify-center inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                                Approve All
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($payrollComputed)
                                         <a href="{{ route('payroll.computation.show', $period) }}" class="w-full block text-center mt-2 px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring ring-blue-300 active:text-gray-800 active:bg-gray-50 transition ease-in-out duration-150">
                                            View Details
                                        </a>
                                    @endif
                                @else
                                    <div class="text-sm text-gray-500 italic text-center mb-4">
                                        <span class="text-red-500 font-semibold">Option A:</span> Approve DTRs first (Recommended)
                                    </div>
                                    
                                    <div class="border-t border-gray-200 mt-4 pt-4">
                                        <p class="text-xs text-gray-600 mb-2 font-semibold">Option B: Manual Mode</p>
                                        <form action="{{ route('payroll.computation.compute', $period) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="manual_mode" value="1">
                                            <button type="submit" class="w-full justify-center inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150" onclick="return confirm('WARNING: You are skipping the automated computation!\n\nThis will generate blank payroll records (₱0.00 amount) which you must fill in MANUALLY.\n\nAre you absolutely sure?')">
                                                Initialize Manual Entry
                                            </button>
                                        </form>
                                        <p class="text-xs text-gray-400 mt-1 text-center">Use this if you compute salary in Excel/Paper</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PHASE 3: Payslip & Finalization --}}
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border-t-4 {{ $postingStats['posted'] > 0 ? 'border-green-500' : ($allPayrollApproved ? 'border-blue-500' : 'border-gray-200 opacity-75') }}">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Phase 3: Payslip</h3>
                             @if(!$allPayrollApproved)
                                <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2.5 py-0.5 rounded">Locked</span>
                            @elseif($postingStats['released'] == $payrollStats['total'] && $payrollStats['total'] > 0)
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">Completed</span>
                            @else
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">Ready</span>
                            @endif
                        </div>

                        <div class="space-y-4">
                            <div class="text-sm text-gray-600">
                                <p>Distribute payslips and release payroll.</p>
                            </div>
                            
                            @if($postingStats['posted'] > 0 || $postingStats['released'] > 0)
                                <div class="bg-gray-50 p-4 rounded-md text-sm">
                                    <div class="flex justify-between mb-1">
                                        <span>Posted to Portal:</span>
                                        <span class="font-bold text-green-600">{{ $postingStats['posted'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Released (Paid):</span>
                                        <span class="font-bold text-blue-600">{{ $postingStats['released'] }}</span>
                                    </div>
                                </div>
                            @endif

                            <div class="border-t pt-4 space-y-2">
                                @if($allPayrollApproved)
                                    <form action="{{ route('payslip.admin.bulk-generate', $period) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full justify-center inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                            Generate PDFs
                                        </button>
                                    </form>

                                    <form action="{{ route('payroll.computation.bulk-post', $period) }}" method="POST" onsubmit="return confirm('This will make payslips visible to employees on their portal. Continue?');">
                                        @csrf
                                        <button type="submit" class="w-full justify-center inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                            Post to Portal
                                        </button>
                                    </form>

                                    <form action="{{ route('payroll.computation.bulk-release', $period) }}" method="POST" onsubmit="return confirm('Mark all payrolls as RELEASED/PAID?');">
                                        @csrf
                                        <button type="submit" class="w-full justify-center inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                            Mark as Released
                                        </button>
                                    </form>
                                @else
                                    <div class="text-sm text-gray-500 italic text-center">Complete Phase 2 to unlock.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            
            <div class="mt-8 flex justify-center">
                 <a href="{{ route('payroll.computation.dashboard') }}" class="text-gray-600 hover:text-gray-900 underline">Back to Dashboard</a>
            </div>
        </div>
    </div>
</x-app-layout>
