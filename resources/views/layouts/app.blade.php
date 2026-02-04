<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ 
    sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false',
    appLoading: false,
    notifications: [],
    unreadCount: 0,
    showDebugModal: false,
    debugContent: '',
    addNotification(type, message, debugData = null) {
        const id = Date.now();
        this.notifications.push({ id, type, message, debugData });
        setTimeout(() => {
            this.notifications = this.notifications.filter(n => n.id !== id);
        }, 5000);
    },
    openDebug(data) {
        this.debugContent = typeof data === 'object' ? JSON.stringify(data, null, 2) : data;
        this.showDebugModal = true;
    },
    fetchUnreadCount() {
        fetch('{{ route('notifications.unread-count') }}')
            .then(r => r.json())
            .then(d => this.unreadCount = d.count);
    }
}" x-init="
    $watch('sidebarOpen', val => localStorage.setItem('sidebarOpen', val));
    fetchUnreadCount();
    setInterval(() => fetchUnreadCount(), 30000);
    
    @if(session('info')) addNotification('info', '{{ session('info') }}'); @endif
">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MEBS HIYAS') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            [x-cloak] { display: none !important; }
            
            /* Sidebar Animations */
            .sidebar-transition {
                transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .sidebar-expanded {
                width: 288px !important;
            }
            
            .sidebar-collapsed {
                width: 80px !important;
            }
            
            .content-transition {
                transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .content-expanded {
                margin-left: 288px !important;
            }
            
            .content-collapsed {
                margin-left: 80px !important;
            }
            
            /* Mobile responsiveness */
            @media (max-width: 1023px) {
                .sidebar-expanded {
                    width: 288px !important;
                    z-index: 40 !important;
                }
                
                .sidebar-collapsed {
                    width: 0px !important;
                    transform: translateX(-100%);
                }
                
                .content-expanded,
                .content-collapsed {
                    margin-left: 0 !important;
                }
            }
            
            /* Text animations */
            .sidebar-text {
                transition: opacity 0.2s ease-in-out, transform 0.2s ease-in-out;
            }
            
            .sidebar-text-hidden {
                opacity: 0;
                transform: translateX(-10px);
            }
            
            .sidebar-text-visible {
                opacity: 1;
                transform: translateX(0);
            }
            
            /* Toggle button hover effect */
            .toggle-btn {
                transition: all 0.2s ease-in-out;
            }
            
            .toggle-btn:hover {
                background-color: rgba(59, 130, 246, 0.1);
                transform: scale(1.05);
            }
            
            /* Tooltip styles */
            .tooltip {
                position: absolute;
                left: calc(100% + 10px);
                top: 50%;
                transform: translateY(-50%);
                background-color: #374151;
                color: white;
                padding: 8px 12px;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 500;
                white-space: nowrap;
                z-index: 1000;
                pointer-events: none;
                opacity: 0;
                transition: opacity 0.2s ease-in-out;
            }
            
            .tooltip:after {
                content: '';
                position: absolute;
                right: 100%;
                top: 50%;
                border: 5px solid transparent;
                border-right-color: #374151;
                transform: translateY(-50%);
            }
            
            .nav-item:hover .tooltip {
                opacity: 1;
            }
            
            /* Hide tooltips when sidebar is open or on mobile */
            .sidebar-open .tooltip,
            @media (max-width: 1023px) {
                .tooltip {
                    display: none;
                }
            }
            
            /* Improved scrollbar for sidebar */
            #sidebar-nav::-webkit-scrollbar {
                width: 6px;
            }
            
            #sidebar-nav::-webkit-scrollbar-track {
                background: rgba(55, 65, 81, 0.3);
                border-radius: 3px;
            }
            
            #sidebar-nav::-webkit-scrollbar-thumb {
                background: rgba(156, 163, 175, 0.5);
                border-radius: 3px;
            }
            
            #sidebar-nav::-webkit-scrollbar-thumb:hover {
                background: rgba(156, 163, 175, 0.8);
            }

            /* Global App Styling Improvements */
            [x-cloak] { display: none !important; }

            /* Global Loader Animation */
            .loader-overlay {
                position: fixed;
                inset: 0;
                background: rgba(255, 255, 255, 0.7);
                backdrop-filter: blur(2px);
                z-index: 9999;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                transition: opacity 0.3s ease;
            }

            .loader-box {
                background: white;
                padding: 2rem;
                border-radius: 1rem;
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 1rem;
            }

            .spinner {
                width: 40px;
                height: 40px;
                border: 3px solid #f3f3f3;
                border-top: 3px solid #3b82f6;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            /* Toast Notifications Overlay */
            .toast-wrapper {
                position: fixed;
                bottom: 1.5rem;
                right: 1.5rem;
                z-index: 10000;
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
                pointer-events: none;
            }

            .toast-notification {
                pointer-events: auto;
                min-width: 320px;
                max-width: 480px;
                background: white;
                color: #1f2937;
                padding: 1rem;
                border-radius: 0.75rem;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                display: flex;
                align-items: center;
                gap: 0.75rem;
                animation: toast-in 0.3s ease-out;
                border-left-width: 4px;
            }

            @keyframes toast-in {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }

            .toast-success { border-left-color: #10b981; }
            .toast-error { border-left-color: #ef4444; }
            .toast-warning { border-left-color: #f59e0b; }
            .toast-info { border-left-color: #3b82f6; }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-100 transition-colors duration-200">
        <div class="min-h-screen flex">
            <!-- Left Sidebar -->
            <aside :class="sidebarOpen ? 'sidebar-expanded sidebar-open' : 'sidebar-collapsed'" class="fixed inset-y-0 left-0 bg-gray-800 text-white sidebar-transition z-30 flex flex-col">
                <!-- Logo Section with Notification Bell and Toggle -->
                <div class="h-16 flex items-center justify-between px-4 border-b border-gray-700">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center font-bold text-lg flex-shrink-0">
                                M
                            </div>
                            <span x-show="sidebarOpen" x-cloak class="font-bold text-lg sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">MEBS HIYAS</span>
                        </a>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Sidebar Toggle Button -->
                        <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg hover:bg-gray-700 transition-colors toggle-btn" title="Toggle Sidebar">
                            <svg :class="!sidebarOpen ? 'rotate-180' : ''" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                            </svg>
                        </button>
                        
                    </div>
                </div>

                <!-- Navigation Links -->
                <nav id="sidebar-nav" class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                    <!-- Notifications -->
                    <div class="relative nav-item">
                        <a href="{{ route('notifications.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('notifications.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            <div class="w-8 h-8 flex items-center justify-center flex-shrink-0 relative">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <template x-if="unreadCount > 0">
                                    <span class="absolute top-0 right-0 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-sm">
                                        <span x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
                                    </span>
                                </template>
                            </div>
                            <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Notifications</span>
                        </a>
                        <div x-show="!sidebarOpen" class="tooltip">Notifications</div>
                    </div>

                    <!-- Dashboard -->
                    <div class="relative nav-item">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            <div class="w-8 h-8 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Dashboard</span>
                        </a>
                        <div x-show="!sidebarOpen" class="tooltip">Dashboard</div>
                    </div>

                    <!-- Attendance -->
                    <div class="relative nav-item">
                        <a href="{{ route('attendance.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('attendance.index', 'attendance.history') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            <div class="w-8 h-8 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Attendance</span>
                        </a>
                        <div x-show="!sidebarOpen" class="tooltip">Attendance</div>
                    </div>

                    <!-- My DTR -->
                    <div class="relative nav-item">
                        <a href="{{ route('dtr.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('dtr.index') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            <div class="w-8 h-8 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">My DTR</span>
                        </a>
                        <div x-show="!sidebarOpen" class="tooltip">My DTR</div>
                    </div>

                    <!-- Payslips -->
                    <div class="relative nav-item">
                        <a href="{{ route('payroll.my-payslips') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('payroll.my-payslips', 'payroll.my-payslip') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            <div class="w-8 h-8 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Payslips</span>
                        </a>
                        <div x-show="!sidebarOpen" class="tooltip">Payslips</div>
                    </div>

                    <!-- Transactions -->
                    <div class="relative nav-item">
                        <a href="{{ route('transactions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('transactions.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            <div class="w-8 h-8 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Transactions</span>
                        </a>
                        <div x-show="!sidebarOpen" class="tooltip">Transactions</div>
                    </div>

                    <!-- My Concerns -->
                    <div class="relative nav-item">
                        <a href="{{ route('concerns.my') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('concerns.my', 'concerns.user-create', 'concerns.user-show') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            <div class="w-8 h-8 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">My Concerns</span>
                        </a>
                        <div x-show="!sidebarOpen" class="tooltip">My Concerns</div>
                    </div>

                    @if(auth()->user()->isAdmin() || auth()->user()->isHr())
                        <!-- HR Management Section -->
                        <div class="pt-4 mt-4 border-t border-gray-700">
                            <p x-show="sidebarOpen" x-cloak class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">HR Management</p>
                            <p x-show="!sidebarOpen" x-cloak class="px-3 text-center mb-2">👥</p>
                        </div>

                        <!-- Sites -->
                        <div class="relative nav-item">
                            <a href="{{ route('sites.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('sites.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <div class="w-8 h-8 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Sites</span>
                            </a>
                            <div x-show="!sidebarOpen" class="tooltip">Sites</div>
                        </div>

                        <!-- Accounts -->
                        <div class="relative nav-item">
                            <a href="{{ route('accounts.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('accounts.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <div class="w-8 h-8 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Accounts</span>
                            </a>
                            <div x-show="!sidebarOpen" class="tooltip">Accounts</div>
                        </div>

                        <!-- Schedules -->
                        <div class="relative nav-item">
                            <a href="{{ route('schedules.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('schedules.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <div class="w-8 h-8 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Schedules</span>
                            </a>
                            <div x-show="!sidebarOpen" class="tooltip">Schedules</div>
                        </div>

                        <!-- Employees -->
                        <div class="relative nav-item">
                            <a href="{{ route('employees.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('employees.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <div class="w-8 h-8 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                    </svg>
                                </div>
                                <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Employees</span>
                            </a>
                            <div x-show="!sidebarOpen" class="tooltip">Employees</div>
                        </div>

                        <!-- Attendance Records -->
                        <div class="relative nav-item">
                            <a href="{{ route('attendance.manage') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('attendance.manage', 'attendance.create', 'attendance.edit', 'attendance.show') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <div class="w-8 h-8 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Attendance Records</span>
                            </a>
                            <div x-show="!sidebarOpen" class="tooltip">Attendance Records</div>
                        </div>

                        <!-- DTR Management -->
                        <div class="relative nav-item">
                            <a href="{{ route('dtr.admin-index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('dtr.admin-index', 'dtr.admin-show') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <div class="w-8 h-8 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">DTR Management</span>
                            </a>
                            <div x-show="!sidebarOpen" class="tooltip">DTR Management</div>
                        </div>

                        <!-- DTR Approval (Automation) -->
                        <div class="relative nav-item">
                            <a href="{{ route('dtr-approval.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('dtr-approval.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <div class="w-8 h-8 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">DTR Approval</span>
                            </a>
                            <div x-show="!sidebarOpen" class="tooltip">DTR Approval</div>
                        </div>

                        <!-- Leave Requests -->
                        <div class="relative nav-item">
                            <a href="{{ route('leaves.manage') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('leaves.manage') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <div class="w-8 h-8 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Leave Requests</span>
                            </a>
                            <div x-show="!sidebarOpen" class="tooltip">Leave Requests</div>
                        </div>

                        <!-- Leave Types -->
                        <div class="relative nav-item">
                            <a href="{{ route('leave-types.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('leave-types.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Leave Types</span>
                            </a>
                            <div x-show="!sidebarOpen" class="tooltip">Leave Types</div>
                        </div>

                        <!-- Leave Credits -->
                        <div class="relative nav-item">
                            <a href="{{ route('leave-credits.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('leave-credits.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Leave Credits</span>
                            </a>
                            <div x-show="!sidebarOpen" class="tooltip">Leave Credits</div>
                        </div>

                        <!-- Payroll Section -->
                        <div class="pt-4 mt-4 border-t border-gray-700">
                            <p x-show="sidebarOpen" x-cloak class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Payroll</p>
                            <p x-show="!sidebarOpen" x-cloak class="px-3 text-center mb-2">💰</p>
                        </div>

                        <!-- Payroll Periods -->
                        <div class="relative nav-item">
                            <a href="{{ route('payroll.periods') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('payroll.periods', 'payroll.create-period', 'payroll.show-period') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Payroll Periods</span>
                            </a>
                            <div x-show="!sidebarOpen" class="tooltip">Payroll Periods</div>
                        </div>

                        <!-- All Payrolls -->
                        <div class="relative nav-item">
                            <a href="{{ route('payroll.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('payroll.index', 'payroll.show') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">All Payrolls</span>
                            </a>
                            <div x-show="!sidebarOpen" class="tooltip">All Payrolls</div>
                        </div>

                        <!-- Payroll Computation (Automation) -->
                        <div class="relative nav-item">
                            <a href="{{ route('payroll.computation.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('payroll.computation.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Payroll Automation</span>
                            </a>
                            <div x-show="!sidebarOpen" class="tooltip">Payroll Automation</div>
                        </div>

                        <!-- Tools & Reports Section -->
                        <div class="pt-4 mt-4 border-t border-gray-700">
                            <p x-show="sidebarOpen" x-cloak class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Tools & Reports</p>
                            <p x-show="!sidebarOpen" x-cloak class="px-3 text-center mb-2">📈</p>
                        </div>

                        <!-- Automation Dashboard -->
                        <div class="relative nav-item">
                            <a href="{{ route('automation.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('automation.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Automation</span>
                            </a>
                            <div x-show="!sidebarOpen" class="tooltip">Automation Dashboard</div>
                        </div>

                        <!-- Analytics Dashboard -->
                        <div class="relative nav-item">
                            <a href="{{ route('analytics.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('analytics.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Analytics</span>
                            </a>
                            <div x-show="!sidebarOpen" class="tooltip">Analytics</div>
                        </div>

                        <!-- Holidays -->
                        <div class="relative nav-item">
                            <a href="{{ route('holidays.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('holidays.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                                <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Holidays</span>
                            </a>
                            <div x-show="!sidebarOpen" class="tooltip">Holidays</div>
                        </div>

                        <!-- Reports -->
                        <div class="relative nav-item">
                            <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('reports.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Reports</span>
                            </a>
                            <div x-show="!sidebarOpen" class="tooltip">Reports</div>
                        </div>

                        <!-- Audit Logs -->
                        <div class="relative nav-item">
                            <a href="{{ route('audit-logs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('audit-logs.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                                <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Audit Logs</span>
                            </a>
                            <div x-show="!sidebarOpen" class="tooltip">Audit Logs</div>
                        </div>

                        <!-- Timekeeping Management -->
                        <div class="relative nav-item">
                            <a href="{{ route('timekeeping.admin-index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('timekeeping.admin-index') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Timekeeping Mgmt</span>
                            </a>
                            <div x-show="!sidebarOpen" class="tooltip">Timekeeping Management</div>
                        </div>

                        @if(auth()->user()->isAdmin())
                            <!-- Admin Section -->
                            <div class="pt-4 mt-4 border-t border-gray-700">
                                <p x-show="sidebarOpen" x-cloak class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Administration</p>
                                <p x-show="!sidebarOpen" x-cloak class="px-3 text-center mb-2">⚙️</p>
                            </div>

                            <!-- Settings -->
                            <div class="relative nav-item">
                                <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('settings.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Settings</span>
                                </a>
                                <div x-show="!sidebarOpen" class="tooltip">Settings</div>
                            </div>

                            <!-- Backups -->
                            <div class="relative nav-item">
                                <a href="{{ route('backups.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('backups.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                                    </svg>
                                    <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Backups</span>
                                </a>
                                <div x-show="!sidebarOpen" class="tooltip">Backups</div>
                            </div>

                            <!-- Concerns & Tickets -->
                            <div class="relative nav-item">
                                <a href="{{ route('concerns.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('concerns.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                    <span x-show="sidebarOpen" x-cloak class="sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">Concerns & Tickets</span>
                                </a>
                                <div x-show="!sidebarOpen" class="tooltip">Concerns & Tickets</div>
                            </div>
                        @endif
                    @endif
                </nav>

                <!-- User Section at Bottom -->
                <div class="border-t border-gray-700 p-3">
                    <!-- User Info -->
                    <div x-data="{ userMenuOpen: false }" class="relative">
                        <button @click="userMenuOpen = !userMenuOpen" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition">
                            <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-teal-600 rounded-full flex items-center justify-center text-sm font-medium flex-shrink-0">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div x-show="sidebarOpen" x-cloak class="flex-1 text-left min-w-0 sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'">
                                <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-400 capitalize">{{ auth()->user()->role }}</p>
                            </div>
                            <svg x-show="sidebarOpen" x-cloak class="w-4 h-4 flex-shrink-0 sidebar-text" :class="sidebarOpen ? 'sidebar-text-visible' : 'sidebar-text-hidden'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- User Dropdown Menu -->
                        <div x-show="userMenuOpen" 
                             x-cloak
                             @click.away="userMenuOpen = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute bottom-full left-0 right-0 mb-2 bg-gray-700 rounded-lg shadow-lg overflow-hidden">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm text-gray-300 hover:bg-gray-600 hover:text-white transition">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Profile
                                </span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-gray-300 hover:bg-gray-600 hover:text-white transition">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Log Out
                                    </span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Mobile Sidebar Overlay -->
            <div x-show="sidebarOpen && window.innerWidth < 1024" @click="sidebarOpen = false" class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden" x-cloak></div>

            <!-- Main Content -->
            <div :class="sidebarOpen ? 'content-expanded' : 'content-collapsed'" class="flex-1 bg-gray-100 content-transition min-h-screen">
                <!-- Mobile Header -->
                <header class="lg:hidden bg-white shadow-sm h-16 flex items-center px-4 sticky top-0 z-10">
                    <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg hover:bg-gray-100 transition">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <span class="ml-4 font-bold text-lg text-gray-800">MEBS HIYAS</span>
                </header>
                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white shadow transition-colors duration-200">
                        <div class="max-w-full mx-auto py-4 px-4 sm:px-6">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main class="p-3 lg:p-4">
                    @if(isset($slot))
                        {{ $slot }}
                    @endif
                    @yield('content')
                </main>
            </div>
        </div>

        <!-- Sidebar Scroll Position Preservation -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sidebarNav = document.getElementById('sidebar-nav');
                
                if (sidebarNav) {
                    // Restore scroll position on page load
                    const savedScrollPos = sessionStorage.getItem('sidebarScrollPos');
                    if (savedScrollPos) {
                        sidebarNav.scrollTop = parseInt(savedScrollPos);
                    }
                    
                    // Save scroll position before leaving
                    sidebarNav.addEventListener('scroll', function() {
                        sessionStorage.setItem('sidebarScrollPos', sidebarNav.scrollTop);
                    });
                    
                    // Also save on link clicks
                    const navLinks = sidebarNav.querySelectorAll('a');
                    navLinks.forEach(function(link) {
                        link.addEventListener('click', function() {
                            sessionStorage.setItem('sidebarScrollPos', sidebarNav.scrollTop);
                        });
                    });
                }
            });
        </script>

        <!-- Global Loader Overlay -->
        <template x-if="appLoading">
            <div class="loader-overlay" x-transition.opacity>
                <div class="loader-box">
                    <div class="spinner"></div>
                    <p class="text-sm font-medium text-gray-600">Processing your request...</p>
                </div>
            </div>
        </template>

        <!-- global Notification Toasts -->
        <div class="toast-wrapper">
            <template x-for="notif in notifications" :key="notif.id">
                <div class="toast-notification" :class="'toast-' + notif.type" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100">
                    <!-- Icon -->
                    <div class="flex-shrink-0">
                        <template x-if="notif.type === 'success'">
                            <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </template>
                        <template x-if="notif.type === 'error'">
                            <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                    </div>
                    <!-- Message -->
                    <div class="flex-1">
                        <p class="text-sm font-medium" x-text="notif.message"></p>
                    </div>
                    <!-- Close / Debug -->
                    <div class="flex items-center gap-2">
                        <template x-if="notif.type === 'error' && notif.debugData">
                            <button @click="openDebug(notif.debugData)" class="text-xs text-blue-600 hover:underline">Debug</button>
                        </template>
                        <button @click="notifications = notifications.filter(n => n.id !== notif.id)" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- global Debug Modal -->
        <div x-show="showDebugModal" class="fixed inset-0 z-[11000] overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div @click="showDebugModal = false" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
                <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="px-4 py-5 bg-gray-50 sm:px-6 flex justify-between items-center">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">System Debug Information</h3>
                        <button @click="showDebugModal = false" class="text-gray-400 hover:text-gray-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="px-4 py-5 bg-white sm:p-6">
                        <pre class="p-4 bg-gray-900 text-green-400 rounded-lg overflow-x-auto text-sm font-mono" x-text="debugContent"></pre>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="showDebugModal = false" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- threshold Loading Logic -->
        <script>
            window.addEventListener('beforeunload', () => {
                // Show loader after 2s only if the user requested a long process
                setTimeout(() => {
                    // Check if still loading
                    document.dispatchEvent(new CustomEvent('show-system-loader'));
                }, 1500); // 1.5s as a cushion to meet user's '2s' request effectively
            });

            // Listen for manual triggers or threshold
            document.addEventListener('show-system-loader', () => {
                const htmlData = document.querySelector('html').__x.$data;
                if (htmlData) htmlData.appLoading = true;
            });

            // Intercept all forms to show loader
            document.addEventListener('submit', (e) => {
                // For forms, show a bit faster as they are usually data actions
                setTimeout(() => {
                    const htmlData = document.querySelector('html').__x.$data;
                    if (htmlData) htmlData.appLoading = true;
                }, 500);
            });

            // Global Error Catcher for JS
            window.onerror = function(message, source, lineno, colno, error) {
                const htmlData = document.querySelector('html').__x.$data;
                if (htmlData) {
                    htmlData.addNotification('error', 'A system error occurred.', {
                        message: message,
                        source: source,
                        line: lineno,
                        column: colno,
                        stack: error ? error.stack : 'N/A'
                    });
                }
                return false;
            };
        </script>
    </body>
</html>
