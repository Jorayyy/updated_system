<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <h2 class="font-black text-2xl text-slate-800 uppercase tracking-tighter">
                    Payroll <span class="text-indigo-600">Pipeline</span>
                </h2>
                <div class="hidden md:flex items-center bg-slate-100 rounded-full px-3 py-1 border border-slate-200">
                    <div class="flex items-center gap-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        System Ready
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('payroll.create-period') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 transition shadow-sm border-b-4 border-indigo-800 active:border-b-0 active:translate-y-1">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Pay Period
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Pipeline Visualizer --}}
            <div class="mb-8 relative">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t-2 border-slate-200 border-dashed"></div>
                </div>
                <div class="relative flex justify-between">
                    <div class="bg-white px-4 flex flex-col items-center gap-2">
                        <div class="w-10 h-10 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center border-2 border-yellow-200 shadow-sm transition-transform hover:scale-110">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Step 1: DTR</span>
                    </div>
                    <div class="bg-white px-4 flex flex-col items-center gap-2">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center border-2 border-blue-200 shadow-sm transition-transform hover:scale-110">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Step 2: Compute</span>
                    </div>
                    <div class="bg-white px-4 flex flex-col items-center gap-2">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center border-2 border-indigo-200 shadow-sm transition-transform hover:scale-110">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Step 3: Review</span>
                    </div>
                    <div class="bg-white px-4 flex flex-col items-center gap-2">
                        <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center border-2 border-green-200 shadow-sm transition-transform hover:scale-110">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Step 4: Payslips</span>
                    </div>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="mb-4 flex items-center p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg shadow-sm">
                    <div class="flex-shrink-0 text-emerald-500 uppercase tracking-widest font-black text-xs mr-3">Success</div>
                    <div class="text-sm font-bold text-emerald-800">{{ session('success') }}</div>
                </div>
            @endif

            {{-- Main Workflow Area --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {{-- LEFT COLUMN: Active Pipeline --}}
                <div class="lg:col-span-8 space-y-8">
                    
                    {{-- PHASE 2: PROCESSING (Top Priority) --}}
                    @if($processingPeriods->isNotEmpty())
                        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-blue-100 animate-in fade-in slide-in-from-bottom-4 duration-500">
                            <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white flex justify-between items-center">
                                <div>
                                    <h3 class="text-xl font-black uppercase tracking-tighter italic">Active Computations</h3>
                                    <p class="text-blue-100 text-xs font-bold uppercase tracking-widest">Real-time status tracking</p>
                                </div>
                                <div class="bg-blue-400/20 px-3 py-1 rounded-full border border-blue-400/30">
                                    <span class="text-[10px] font-black uppercase tracking-widest animate-pulse">Running...</span>
                                </div>
                            </div>
                            <div class="p-6 space-y-6">
                                @foreach($processingPeriods as $period)
                                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 shadow-inner" id="period-card-{{ $period->id }}">
                                        <div class="flex flex-wrap justify-between items-start gap-4 mb-4">
                                            <div>
                                                <h4 class="font-black text-slate-800 uppercase tracking-tight text-lg">
                                                    {{ $period->period_label }}
                                                </h4>
                                                <div class="flex gap-2 mt-1">
                                                    <span class="bg-slate-200 text-slate-600 text-[10px] font-black px-2 py-0.5 rounded uppercase tracking-wider">
                                                        {{ $period->payrollGroup->name ?? 'Global' }}
                                                    </span>
                                                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">
                                                        Started {{ $period->updated_at->diffForHumans() }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex gap-2">
                                                <form action="{{ route('payroll.computation.reset', $period) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="text-[10px] font-black text-red-500 uppercase tracking-widest hover:underline" onclick="return confirm('Stop and reset?')">Stop Job</button>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="relative pt-1">
                                            <div class="flex mb-2 items-center justify-between">
                                                <div>
                                                    <span id="progress-msg-{{ $period->id }}" class="text-[10px] font-black uppercase tracking-widest text-blue-600 bg-blue-50 px-2 py-1 rounded border border-blue-100">
                                                        Connecting...
                                                    </span>
                                                </div>
                                                <div class="text-right">
                                                    <span id="progress-pct-{{ $period->id }}" class="text-sm font-black text-blue-700">0%</span>
                                                </div>
                                            </div>
                                            <div class="overflow-hidden h-4 mb-4 text-xs flex rounded-full bg-slate-200 shadow-inner">
                                                <div style="width:0%" id="progress-bar-{{ $period->id }}" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-600 transition-all duration-700 animate-pulse"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- PHASE 1: DTR BLOCKDOWN (Step 1) --}}
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-slate-200">
                        <div class="p-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                            <div>
                                <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest italic flex items-center">
                                    <span class="w-6 h-6 rounded bg-yellow-400 flex items-center justify-center text-white mr-2 text-xs italic">1</span>
                                    DTR Lockdown & Prep
                                </h3>
                            </div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $readyPeriods->count() + $pendingPeriods->count() }} Periods</span>
                        </div>
                        
                        <div class="divide-y divide-slate-100">
                            {{-- Periods Waiting for Approvals --}}
                            @foreach($pendingPeriods as $period)
                                @php $progressPercent = $period->total_dtrs > 0 ? round(($period->approved_dtrs / $period->total_dtrs) * 100) : 0; @endphp
                                <div class="p-6 hover:bg-slate-50 transition-colors">
                                    <div class="flex flex-wrap items-center justify-between gap-4">
                                        <div class="space-y-1 min-w-[200px]">
                                            <h4 class="font-black text-slate-800 leading-tight">{{ $period->period_label }}</h4>
                                            <span class="inline-block bg-yellow-100 text-yellow-700 text-[10px] font-black px-2 py-0.5 rounded uppercase tracking-wider border border-yellow-200">
                                                Needs Approval
                                            </span>
                                        </div>
                                        
                                        <div class="flex-1 max-w-xs px-4">
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Approvals</span>
                                                <span class="text-[10px] font-black text-slate-800 italic">{{ $progressPercent }}%</span>
                                            </div>
                                            <div class="overflow-hidden h-2 text-xs flex rounded bg-slate-100 border border-slate-200 shadow-inner">
                                                <div style="width:{{ $progressPercent }}%" class="bg-yellow-400 transition-all duration-1000"></div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('dtr-approval.index', ['payroll_period_id' => $period->id]) }}" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:underline">
                                                Go to DTR Center →
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Periods Ready to Compute --}}
                            @foreach($readyPeriods as $period)
                                <div class="p-6 bg-emerald-50/30 border-l-4 border-emerald-500">
                                    <div class="flex flex-wrap items-center justify-between gap-4">
                                        <div class="space-y-1">
                                            <h4 class="font-black text-slate-800 leading-tight">{{ $period->period_label }}</h4>
                                            <span class="inline-block bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-0.5 rounded uppercase tracking-wider border border-emerald-200">
                                                DTRs Locked {{ $period->total_dtrs }}/{{ $period->total_dtrs }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-4">
                                            <form action="{{ route('payroll.computation.compute', $period) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-6 py-2 bg-emerald-600 text-white rounded-lg text-[11px] font-black uppercase tracking-widest hover:bg-emerald-700 transition shadow-sm border-b-4 border-emerald-800 active:border-b-0 active:translate-y-1">
                                                    Start Computation
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            
                            @if($readyPeriods->isEmpty() && $pendingPeriods->isEmpty())
                                <div class="p-12 text-center">
                                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-slate-200 border-dashed">
                                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                    </div>
                                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">No Active Workflows</p>
                                    <p class="text-[10px] text-slate-400 font-medium">Create a new pay period to start the pipeline.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- PHASE 3: COMPLETED / RELEASE (Step 3 & 4) --}}
                    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
                        <div class="p-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest italic flex items-center">
                                <span class="w-6 h-6 rounded bg-indigo-500 flex items-center justify-center text-white mr-2 text-xs italic">3</span>
                                Computed & Finalized
                            </h3>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Archive</span>
                        </div>
                        <div class="divide-y divide-slate-100">
                            @foreach($completedPeriods as $period)
                                <div class="p-6 hover:bg-slate-50 transition-colors flex flex-wrap items-center justify-between gap-6">
                                    <div class="space-y-1">
                                        <h4 class="font-black text-slate-800 leading-tight">{{ $period->period_label }}</h4>
                                        <div class="flex gap-2">
                                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">
                                                Computed {{ optional($period->payroll_computed_at)->format('M d, H:i') ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex gap-3">
                                        <a href="{{ route('payroll.computation.show', $period) }}" class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 border border-slate-200 transition">
                                            View Register
                                        </a>
                                        <a href="{{ route('payroll.report', $period) }}" class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-indigo-100 border border-indigo-200 transition">
                                            Download PDF
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>

                {{-- RIGHT COLUMN: Context & Stats --}}
                <div class="lg:col-span-4 space-y-8">
                    
                    {{-- Quick Stats Card --}}
                    <div class="bg-slate-800 rounded-2xl p-6 text-white shadow-xl relative overflow-hidden group">
                        <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-500/10 rounded-full group-hover:scale-150 transition-transform duration-1000"></div>
                        <h3 class="text-xs font-black uppercase tracking-widest text-indigo-300 italic mb-6">Pipeline Health</h3>
                        
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 italic">Pre-Computation</span>
                                <span class="text-xl font-black italic">{{ $stats['pending_count'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 italic">Computing</span>
                                <span class="text-xl font-black italic text-blue-400">{{ $stats['processing_count'] }}</span>
                            </div>
                            <div class="flex items-center justify-between border-t border-slate-700 pt-4">
                                <span class="text-[10px] font-black uppercase tracking-widest text-indigo-300 italic">Employees Total</span>
                                <span class="text-2xl font-black italic tracking-tighter">{{ $stats['total_employees'] }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Admin Actions --}}
                    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 italic mb-4">Command Center</h3>
                        <div class="grid grid-cols-1 gap-3">
                            <a href="{{ route('payroll-groups.index') }}" class="flex items-center p-3 rounded-xl border border-slate-100 hover:bg-slate-50 transition group">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center mr-3 group-hover:bg-slate-800 group-hover:text-white transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                </div>
                                <span class="text-[10px] font-black text-slate-700 uppercase tracking-widest">Manage Groups</span>
                            </a>
                            <a href="{{ route('payroll.periods') }}" class="flex items-center p-3 rounded-xl border border-slate-100 hover:bg-slate-50 transition group">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center mr-3 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                                <span class="text-[10px] font-black text-slate-700 uppercase tracking-widest">History</span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @if($processingPeriods->isNotEmpty())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const periods = @json($processingPeriods->pluck('id'));
            
            periods.forEach(periodId => {
                const progressBar = document.getElementById(`progress-bar-${periodId}`);
                const progressMsg = document.getElementById(`progress-msg-${periodId}`);
                const progressPct = document.getElementById(`progress-pct-${periodId}`);
                let pollInterval;

                function checkProgress() {
                    fetch(`/payroll/computation/period/${periodId}/progress`)
                        .then(response => response.json())
                        .then(data => {
                            const percentage = data.percentage || 0;
                            if (progressBar) progressBar.style.width = `${percentage}%`;
                            if (progressPct) progressPct.innerText = `${percentage}%`;
                            if (progressMsg) progressMsg.innerText = data.message || `Processing...`;
                            
                            if (data.status === 'completed' || percentage >= 100) {
                                clearInterval(pollInterval);
                                if (progressMsg) progressMsg.innerText = "Completed! Wrapping up...";
                                setTimeout(() => window.location.reload(), 1500);
                            }
                        })
                        .catch(err => console.error('Poll failed:', err));
                }

                checkProgress();
                pollInterval = setInterval(checkProgress, 2000);
            });
        });
    </script>
    @endif
</x-app-layout>
