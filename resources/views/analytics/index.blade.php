<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-gray-800 leading-tight">
                Analytics Dashboard
            </h2>
            <p class="text-sm text-gray-500 mt-1">Insights and trends overview</p>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Total Employees</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900">{{ $stats['total_employees'] }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Avg Attendance Rate</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900">{{ $stats['avg_attendance_rate'] }}%</p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Leave Requests</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900">{{ $stats['pending_leaves'] }}</p>
                            <p class="text-xs text-gray-500">pending</p>
                        </div>
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 1 -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Attendance Trends Chart -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Attendance Trends</h3>
                        <select id="attendance-period" class="text-sm rounded-lg border-gray-300">
                            <option value="7">Last 7 days</option>
                            <option value="30" selected>Last 30 days</option>
                            <option value="90">Last 90 days</option>
                        </select>
                    </div>
                    <div class="h-72">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>

                <!-- Leave Distribution Chart -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Leave Distribution</h3>
                        <span class="text-sm text-gray-500">This Year</span>
                    </div>
                    <div class="h-72">
                        <canvas id="leaveChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Charts Row 2 -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Turnover Rate Chart -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Employee Turnover</h3>
                        <span class="text-sm text-gray-500">Monthly</span>
                    </div>
                    <div class="h-72">
                        <canvas id="turnoverChart"></canvas>
                    </div>
                </div>

                <!-- Payroll Trends Chart -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Monthly Payroll</h3>
                        <span class="text-sm text-gray-500">Last 12 Months</span>
                    </div>
                    <div class="h-72">
                        <canvas id="payrollChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Charts Row 3 -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Department Distribution -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Department Distribution</h3>
                    <div class="h-64">
                        <canvas id="departmentChart"></canvas>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Stats</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-600">Total Payroll (This Month)</span>
                            <span class="text-sm font-semibold text-gray-900">₱{{ number_format($stats['monthly_payroll'], 0) }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-600">Avg Work Hours/Day</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $stats['avg_work_hours'] }} hrs</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-600">Late Arrivals (This Month)</span>
                            <span class="text-sm font-semibold text-yellow-600">{{ $stats['late_arrivals'] }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-600">Upcoming Birthdays</span>
                            <span class="text-sm font-semibold text-purple-600">{{ $stats['upcoming_birthdays'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Birthdays & Anniversaries Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Upcoming Birthdays -->
                <div class="bg-white rounded-xl shadow-sm border border-pink-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-pink-500 to-rose-500 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white">🎂 Upcoming Birthdays</h3>
                            <span class="text-sm text-pink-100">Next 30 days</span>
                        </div>
                    </div>
                    <div class="p-6">
                        @if(count($birthdays) > 0)
                            <div class="space-y-3">
                                @foreach($birthdays as $employee)
                                    <div class="flex items-center gap-3 p-3 bg-pink-50 rounded-lg border border-pink-100">
                                        <div class="w-10 h-10 bg-pink-500 text-white rounded-full flex items-center justify-center font-bold">
                                            {{ substr($employee->name, 0, 1) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-gray-900 truncate">{{ $employee->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $employee->birthday ? $employee->birthday->format('M d') : 'N/A' }}</p>
                                        </div>
                                        @if($employee->birthday)
                                            @php
                                                $bday = \Carbon\Carbon::parse($employee->birthday)->setYear(now()->year);
                                                if ($bday->isPast() && !$bday->isToday()) {
                                                    $bday->addYear();
                                                }
                                                $daysUntil = (int)now()->diffInDays($bday, false);
                                            @endphp
                                            <span class="px-3 py-1 bg-pink-500 text-white rounded-full text-xs font-medium whitespace-nowrap">
                                                @if($daysUntil == 0)
                                                    Today! 🎉
                                                @elseif($daysUntil == 1)
                                                    Tomorrow
                                                @else
                                                    {{ abs($daysUntil) }} days
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0A1.5 1.5 0 013 15.546V5a1 1 0 011-1h16a1 1 0 011 1v10.546zM12 10h.01"/>
                                </svg>
                                <p>No upcoming birthdays in the next 30 days</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Work Anniversaries -->
                <div class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white">🎊 Work Anniversaries</h3>
                            <span class="text-sm text-amber-100">Next 30 days</span>
                        </div>
                    </div>
                    <div class="p-6">
                        @if(count($anniversaries) > 0)
                            <div class="space-y-3">
                                @foreach($anniversaries as $employee)
                                    <div class="flex items-center gap-3 p-3 bg-amber-50 rounded-lg border border-amber-100">
                                        <div class="w-10 h-10 bg-amber-500 text-white rounded-full flex items-center justify-center font-bold">
                                            {{ substr($employee->name, 0, 1) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-gray-900 truncate">{{ $employee->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $employee->date_hired ? $employee->date_hired->format('M d, Y') : 'N/A' }}</p>
                                        </div>
                                        @if($employee->date_hired)
                                            @php
                                                $years = now()->diffInYears($employee->date_hired);
                                            @endphp
                                            <span class="px-3 py-1 bg-amber-500 text-white rounded-full text-xs font-medium whitespace-nowrap">
                                                {{ $years + 1 }} {{ ($years + 1) == 1 ? 'year' : 'years' }}
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p>No upcoming work anniversaries in the next 30 days</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Chart.js global defaults
        Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
        Chart.defaults.color = '#6B7280';
        
        const isDarkMode = document.documentElement.classList.contains('dark');
        const gridColor = isDarkMode ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.05)';

        // Attendance Chart
        const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
        let attendanceChart = new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Present',
                    data: [],
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Late',
                    data: [],
                    borderColor: '#F59E0B',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Absent',
                    data: [],
                    borderColor: '#EF4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { beginAtZero: true, grid: { color: gridColor } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Load attendance data
        async function loadAttendanceData(days = 30) {
            try {
                const response = await fetch(`/analytics/attendance?days=${days}`);
                const data = await response.json();
                attendanceChart.data.labels = data.labels;
                attendanceChart.data.datasets[0].data = data.present;
                attendanceChart.data.datasets[1].data = data.late;
                attendanceChart.data.datasets[2].data = data.absent;
                attendanceChart.update();
            } catch (e) {
                console.error('Error loading attendance data:', e);
            }
        }

        document.getElementById('attendance-period').addEventListener('change', (e) => {
            loadAttendanceData(e.target.value);
        });

        // Leave Distribution Chart
        const leaveCtx = document.getElementById('leaveChart').getContext('2d');
        new Chart(leaveCtx, {
            type: 'doughnut',
            data: {
                labels: @json($leaveData['labels'] ?? []),
                datasets: [{
                    data: @json($leaveData['data'] ?? []),
                    backgroundColor: [
                        '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#6366F1'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                cutout: '60%'
            }
        });

        // Turnover Chart
        const turnoverCtx = document.getElementById('turnoverChart').getContext('2d');
        new Chart(turnoverCtx, {
            type: 'bar',
            data: {
                labels: @json($turnoverData['labels'] ?? []),
                datasets: [{
                    label: 'New Hires',
                    data: @json($turnoverData['hires'] ?? []),
                    backgroundColor: '#10B981',
                    borderRadius: 4
                }, {
                    label: 'Separations',
                    data: @json($turnoverData['separations'] ?? []),
                    backgroundColor: '#EF4444',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { beginAtZero: true, grid: { color: gridColor } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Payroll Chart
        const payrollCtx = document.getElementById('payrollChart').getContext('2d');
        new Chart(payrollCtx, {
            type: 'line',
            data: {
                labels: @json($payrollData['labels'] ?? []),
                datasets: [{
                    label: 'Gross Pay',
                    data: @json($payrollData['gross'] ?? []),
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Net Pay',
                    data: @json($payrollData['net'] ?? []),
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: gridColor },
                        ticks: {
                            callback: value => '₱' + value.toLocaleString()
                        }
                    },
                    x: { grid: { display: false } }
                }
            }
        });

        // Department Chart
        const deptCtx = document.getElementById('departmentChart').getContext('2d');
        new Chart(deptCtx, {
            type: 'pie',
            data: {
                labels: @json($departmentData['labels'] ?? []),
                datasets: [{
                    data: @json($departmentData['data'] ?? []),
                    backgroundColor: [
                        '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#6366F1', '#14B8A6'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } }
            }
        });

        // Initial load
        loadAttendanceData(30);
    </script>
</x-app-layout>
