<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 py-2">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase italic">
                    Intelligence <span class="text-indigo-600">Hub</span>
                </h2>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mt-1">Operational Analytics & Workforce Intelligence</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-white border border-slate-200 px-4 py-2 rounded-2xl shadow-sm">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Cycle Period</span>
                    <span class="text-xs font-black text-slate-900">{{ now()->startOfMonth()->format('M d') }} - {{ now()->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-slate-50 min-h-screen">
        <div class="max-w-[1600px] mx-auto px-4 lg:px-8">
            <!-- Executive Summary: Compact Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                @php
                    $cards = [
                        ['label' => 'Total Staff', 'value' => number_format($stats['total_employees']), 'sub' => 'Active Roster', 'color' => 'indigo', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                        ['label' => 'Avg Attendance', 'value' => $stats['avg_attendance_rate'].'%', 'sub' => 'Reliability Rate', 'color' => 'emerald', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['label' => 'Payroll MTD', 'value' => '₱'.number_format($stats['monthly_payroll'], 0), 'sub' => 'Current Period', 'color' => 'blue', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16V15m0 1v-8m0 0V7'],
                        ['label' => 'Yield Hours', 'value' => ($stats['avg_work_hours'] ?: '8.0').'h', 'sub' => 'Per Employee', 'color' => 'rose', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                    ];
                @endphp
                @foreach($cards as $card)
                    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition-all group">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-10 h-10 bg-{{ $card['color'] }}-50 rounded-xl flex items-center justify-center text-{{ $card['color'] }}-600 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/></svg>
                            </div>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-2 py-0.5 rounded border border-slate-100">{{ $card['sub'] }}</span>
                        </div>
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1.5">{{ $card['label'] }}</h4>
                        <div class="text-2xl font-black text-slate-900 leading-none">{{ $card['value'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-4 gap-6 mb-8">
                <!-- Presence Index (Line Chart) -->
                <div class="xl:col-span-3 bg-white p-8 rounded-[2rem] border border-slate-200 shadow-sm flex flex-col min-h-[500px]">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-xl font-black text-slate-900">Attendance Persistence</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-1">MTD Workforce Reliability Metrics</p>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="text-right">
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Late Tally</p>
                                <p class="text-lg font-black text-rose-500 leading-none">{{ $stats['late_arrivals'] }}</p>
                            </div>
                            <div class="w-[1px] h-8 bg-slate-100"></div>
                            <div class="text-right">
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Health Avg</p>
                                <p class="text-lg font-black text-emerald-500 leading-none">{{ $stats['avg_attendance_rate'] }}%</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1 relative">
                        <canvas id="mainAttendanceChart"></canvas>
                    </div>
                </div>

                <!-- Distribution Panels -->
                <div class="grid grid-cols-1 gap-6">
                    <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm flex flex-col">
                        <h4 class="text-[10px] font-black text-slate-700 uppercase tracking-widest mb-4">Account Spread</h4>
                        <div class="flex-1 relative min-h-[160px]">
                            <canvas id="accountDonutChart"></canvas>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm flex flex-col">
                        <h4 class="text-[10px] font-black text-slate-700 uppercase tracking-widest mb-4">Site Location</h4>
                        <div class="flex-1 relative min-h-[160px]">
                            <canvas id="siteDonutChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <div class="bg-white p-8 rounded-[2rem] border border-slate-200 shadow-sm">
                    <h3 class="text-lg font-black text-slate-900 mb-6">Leave Class Dynamics</h3>
                    <div class="h-[280px]">
                        <canvas id="leaveBarChart"></canvas>
                    </div>
                </div>
                <div class="bg-white p-8 rounded-[2rem] border border-slate-200 shadow-sm">
                    <h3 class="text-lg font-black text-slate-900 mb-6">Staffing Momentum</h3>
                    <div class="h-[280px]">
                        <canvas id="turnoverLineChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Events & Milestones -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 pb-10">
                <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-[11px] font-black text-slate-800 uppercase tracking-[0.2em] flex items-center gap-2">
                            <span class="p-2 bg-pink-50 rounded-lg text-pink-500">🎂</span>
                            Employee Birthdays
                        </h3>
                        <span class="bg-pink-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full">{{ count($birthdays) }} This Month</span>
                    </div>
                    <div class="p-2 flex-1">
                        @forelse($birthdays as $employee)
                            <div class="flex items-center gap-4 p-3 hover:bg-slate-50 rounded-2xl transition-all group">
                                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center font-black text-slate-500 group-hover:bg-pink-500 group-hover:text-white transition-all">
                                    {{ substr($employee->name, 0, 1) }}
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-xs font-black text-slate-900 tracking-tight">{{ $employee->name }}</h4>
                                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ $employee->birthday ? $employee->birthday->format('F d') : 'N/A' }}</p>
                                </div>
                                <div class="text-[9px] font-black text-emerald-500 bg-emerald-50 px-2 py-1 rounded-lg uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">Greet Now</div>
                            </div>
                        @empty
                            <div class="py-12 text-center text-[10px] font-black text-slate-300 uppercase tracking-widest">Safe Skies - No Events</div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-[11px] font-black text-slate-800 uppercase tracking-[0.2em] flex items-center gap-2">
                            <span class="p-2 bg-amber-50 rounded-lg text-amber-500">🎊</span>
                            Longevity Milestones
                        </h3>
                        <span class="bg-amber-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full">{{ count($anniversaries) }} This Month</span>
                    </div>
                    <div class="p-2 flex-1">
                        @forelse($anniversaries as $employee)
                            <div class="flex items-center gap-4 p-3 hover:bg-slate-50 rounded-2xl transition-all group">
                                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center font-black text-slate-500 group-hover:bg-amber-500 group-hover:text-white transition-all">
                                    {{ substr($employee->name, 0, 1) }}
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-xs font-black text-slate-900 tracking-tight">{{ $employee->name }}</h4>
                                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ now()->diffInYears($employee->date_hired) + 1 }} Year Legacy</p>
                                </div>
                                <div class="text-[9px] font-black text-indigo-500 bg-indigo-50 px-2 py-1 rounded-lg uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">Honored</div>
                            </div>
                        @empty
                            <div class="py-12 text-center text-[10px] font-black text-slate-300 uppercase tracking-widest">Steady Horizon</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartConfig = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            };

            // Main Attendance Chart
            new Chart(document.getElementById('mainAttendanceChart'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($attendanceData['labels']) !!},
                    datasets: [{
                        label: 'Attendance %',
                        data: {!! json_encode($attendanceData['data']) !!},
                        borderColor: '#4f46e5',
                        borderWidth: 4,
                        pointRadius: 0,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#4f46e5',
                        fill: true,
                        backgroundColor: (context) => {
                            const ctx = context.chart.ctx;
                            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                            gradient.addColorStop(0, 'rgba(79, 70, 229, 0.1)');
                            gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');
                            return gradient;
                        },
                        tension: 0.4
                    }]
                },
                options: {
                    ...chartConfig,
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            max: 100,
                            grid: { display: true, color: '#f1f5f9' },
                            ticks: { font: { size: 10, weight: '900' }, color: '#94a3b8', callback: v => v + '%' }
                        },
                        x: { 
                            grid: { display: false },
                            ticks: { font: { size: 9, weight: '700' }, color: '#94a3b8' }
                        }
                    }
                }
            });

            // Account Donut
            new Chart(document.getElementById('accountDonutChart'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($accountData['labels']) !!},
                    datasets: [{
                        data: {!! json_encode($accountData['data']) !!},
                        backgroundColor: ['#4f46e5', '#818cf8', '#c7d2fe', '#e0e7ff', '#f5f3ff'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    ...chartConfig,
                    cutout: '75%',
                    plugins: { legend: { display: false } }
                }
            });

            // Site Donut
            new Chart(document.getElementById('siteDonutChart'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($siteData['labels']) !!},
                    datasets: [{
                        data: {!! json_encode($siteData['data']) !!},
                        backgroundColor: ['#10b981', '#34d399', '#6ee7b7', '#a7f3d0', '#ecfdf5'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    ...chartConfig,
                    cutout: '75%',
                    plugins: { legend: { display: false } }
                }
            });

            // Leave Bar
            new Chart(document.getElementById('leaveBarChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($leaveData['labels']) !!},
                    datasets: [{
                        data: {!! json_encode($leaveData['data']) !!},
                        backgroundColor: '#6366f1',
                        borderRadius: 8,
                        barThickness: 20
                    }]
                },
                options: {
                    ...chartConfig,
                    scales: {
                        y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10, weight: '700' }, color: '#94a3b8' } },
                        x: { grid: { display: false }, ticks: { font: { size: 9, weight: '700' }, color: '#94a3b8' } }
                    }
                }
            });

            // Turnover Line
            new Chart(document.getElementById('turnoverLineChart'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($turnoverData['labels']) !!},
                    datasets: [
                        {
                            label: 'Hires',
                            data: {!! json_encode($turnoverData['hires']) !!},
                            borderColor: '#10b981',
                            borderWidth: 3,
                            pointRadius: 4,
                            tension: 0.4
                        },
                        {
                            label: 'Separations',
                            data: {!! json_encode($turnoverData['separations']) !!},
                            borderColor: '#f43f5e',
                            borderWidth: 3,
                            pointRadius: 4,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    ...chartConfig,
                    plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 8, usePointStyle: true, font: { size: 10, weight: '800' } } } },
                    scales: {
                        y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10, weight: '700' }, color: '#94a3b8' } },
                        x: { grid: { display: false }, ticks: { font: { size: 9, weight: '700' }, color: '#94a3b8' } }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>