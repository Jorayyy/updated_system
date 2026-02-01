<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MEBS HIYAS') }} - Login</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased min-h-screen" style="background: linear-gradient(135deg, #87CEEB 0%, #E0F7FA 50%, #FFF9C4 100%);">
        <div class="min-h-screen flex">
            <!-- Left Side - Branding (Desktop Only) -->
            <div class="hidden lg:flex lg:w-1/2 flex-col justify-center items-center p-12 relative overflow-hidden">
                <!-- Decorative circles -->
                <div class="absolute top-20 left-20 w-32 h-32 bg-white opacity-20 rounded-full"></div>
                <div class="absolute bottom-32 right-20 w-48 h-48 bg-yellow-300 opacity-20 rounded-full"></div>
                <div class="absolute top-1/3 right-32 w-24 h-24 bg-blue-400 opacity-20 rounded-full"></div>
                
                <div class="text-center z-10">
                    <!-- Logo Container -->
                    <div class="mb-8">
                        @if(file_exists(public_path('images/logo.png')))
                            <img src="{{ asset('images/logo.png') }}" alt="MEBS Logo" class="w-72 h-auto mx-auto drop-shadow-lg">
                        @else
                            <!-- Text-based Logo -->
                            <div class="flex items-center justify-center mb-4">
                                <span class="text-8xl font-black text-gray-800 tracking-tight">M</span>
                                <div class="relative mx-1">
                                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-cyan-300 to-blue-400 flex items-center justify-center border-4 border-yellow-400 shadow-lg">
                                        <span class="text-2xl font-bold text-gray-800">e</span>
                                    </div>
                                </div>
                                <span class="text-8xl font-black text-gray-800 tracking-tight">BS</span>
                            </div>
                        @endif
                    </div>
                    
                    <h1 class="text-4xl font-bold text-gray-800 mb-1">Mancao E-connect</h1>
                    <h2 class="text-3xl font-bold text-gray-700 mb-3">Business Solutions</h2>
                    <p class="text-xl text-gray-600 font-semibold tracking-wide">CALL CENTER PH</p>
                    
                    <div class="border-t border-gray-400 pt-6 mt-8 max-w-xs mx-auto">
                        <p class="text-gray-600 text-sm font-medium">Human Resource Information System</p>
                        <p class="text-gray-500 text-xs mt-2">MEBS HIYAS v1.0</p>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-6 sm:p-12">
                <!-- Mobile Logo (shown only on small screens) -->
                <div class="lg:hidden text-center mb-6">
                    @if(file_exists(public_path('images/logo.png')))
                        <img src="{{ asset('images/logo.png') }}" alt="MEBS Logo" class="w-48 h-auto mx-auto mb-4">
                    @else
                        <div class="flex items-center justify-center mb-4">
                            <span class="text-5xl font-black text-gray-800">M</span>
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-300 to-blue-400 flex items-center justify-center border-2 border-yellow-400 mx-1">
                                <span class="text-lg font-bold text-gray-800">e</span>
                            </div>
                            <span class="text-5xl font-black text-gray-800">BS</span>
                        </div>
                    @endif
                    <h1 class="text-xl font-bold text-gray-800">Mancao E-connect Business Solutions</h1>
                    <p class="text-gray-600 text-sm font-medium">CALL CENTER PH</p>
                </div>

                <div class="w-full sm:max-w-md bg-white shadow-2xl rounded-2xl overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-6 text-center">
                        <div class="w-16 h-16 bg-white rounded-full mx-auto mb-3 flex items-center justify-center shadow-lg">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white">Welcome Back!</h3>
                        <p class="text-blue-100 mt-1 text-sm">Sign in to continue to MEBS HIYAS</p>
                    </div>
                    
                    <!-- Form Content -->
                    <div class="px-6 py-6">
                        {{ $slot }}
                    </div>
                </div>

                <p class="text-gray-600 text-xs mt-6 text-center">
                    © {{ date('Y') }} Mancao E-connect Business Solutions. All rights reserved.
                </p>
            </div>
        </div>
    </body>
</html>
    </body>
</html>
