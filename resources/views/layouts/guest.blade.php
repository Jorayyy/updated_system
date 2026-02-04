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
    <body class="font-sans text-gray-900 antialiased min-h-screen bg-slate-50 overflow-x-hidden">
        <style>
            @keyframes float-slow {
                0% { transform: translate(0, 0) rotate(0deg); }
                33% { transform: translate(30px, -50px) rotate(2deg); }
                66% { transform: translate(-20px, 20px) rotate(-2deg); }
                100% { transform: translate(0, 0) rotate(0deg); }
            }
            @keyframes float-fast {
                0% { transform: translate(0, 0) scale(1); }
                50% { transform: translate(-40px, 40px) scale(1.1); }
                100% { transform: translate(0, 0) scale(1); }
            }
            @keyframes pulse-soft {
                0%, 100% { opacity: 0.2; transform: scale(1); }
                50% { opacity: 0.4; transform: scale(1.2); }
            }
            @keyframes reveal-up {
                0% { opacity: 0; transform: translateY(20px); filter: blur(5px); }
                100% { opacity: 1; transform: translateY(0); filter: blur(0); }
            }
            .animate-float-slow { animation: float-slow 15s ease-in-out infinite; }
            .animate-float-fast { animation: float-fast 10s ease-in-out infinite; }
            .animate-pulse-soft { animation: pulse-soft 6s ease-in-out infinite; }
            .reveal-text { animation: reveal-up 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
            
            .bg-mesh {
                background-color: #f8fafc;
                background-image: 
                    radial-gradient(at 0% 0%, hsla(199,100%,89%,1) 0, transparent 50%), 
                    radial-gradient(at 50% 0%, hsla(187,100%,92%,1) 0, transparent 50%), 
                    radial-gradient(at 100% 0%, hsla(54,100%,90%,1) 0, transparent 50%);
            }
        </style>

        <div class="min-h-screen flex bg-mesh" x-data="{ show: false }" x-init="setTimeout(() => show = true, 100)">
            <!-- Left Side - Branding (Desktop Only) -->
            <div class="hidden lg:flex lg:w-1/2 flex-col justify-center items-center p-12 relative overflow-hidden">
                <!-- Decorative animated circles -->
                <div class="absolute top-10 left-10 w-48 h-48 bg-blue-300 opacity-20 rounded-full animate-float-slow blur-3xl"></div>
                <div class="absolute bottom-10 right-10 w-64 h-64 bg-yellow-200 opacity-20 rounded-full animate-float-fast blur-3xl"></div>
                <div class="absolute top-1/4 right-20 w-32 h-32 bg-cyan-200 opacity-20 rounded-full animate-pulse-soft blur-2xl"></div>
                <div class="absolute bottom-1/4 left-20 w-40 h-40 bg-indigo-200 opacity-20 rounded-full animate-float-slow blur-2xl" style="animation-delay: -5s"></div>
                
                <div class="text-center z-10" x-show="show" x-transition:enter="transition ease-out duration-1000" x-transition:enter-start="opacity-0 transform translate-y-10" x-transition:enter-end="opacity-100 transform translate-y-0">
                    <!-- Logo Container -->
                    <div class="mb-8 group">
                        @if(file_exists(public_path('images/logo.png')))
                            <img src="{{ asset('images/logo.png') }}" alt="MEBS Logo" class="w-72 h-auto mx-auto drop-shadow-2xl transition-transform duration-500 group-hover:scale-105">
                        @else
                            <!-- Text-based Logo -->
                            <div class="flex items-center justify-center mb-4 transition-all duration-500 group-hover:scale-110">
                                <span class="text-8xl font-black text-slate-800 tracking-tight drop-shadow-sm">M</span>
                                <div class="relative mx-1">
                                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-cyan-300 to-blue-400 flex items-center justify-center border-4 border-yellow-400 shadow-xl animate-bounce">
                                        <span class="text-2xl font-bold text-gray-800">e</span>
                                    </div>
                                </div>
                                <span class="text-8xl font-black text-slate-800 tracking-tight drop-shadow-sm">BS</span>
                            </div>
                        @endif
                    </div>
                    
                    <h1 class="text-5xl font-extrabold text-slate-800 mb-2 reveal-text" style="animation-delay: 0.2s opacity: 0;">Mancao E-connect</h1>
                    <h2 class="text-4xl font-bold text-slate-700 mb-4 reveal-text" style="animation-delay: 0.4s; opacity: 0;">Business Solutions</h2>
                    <p class="text-xl text-blue-600 font-bold tracking-[0.2em] reveal-text" style="animation-delay: 0.6s; opacity: 0;">CALL CENTER PH</p>
                    
                    <div class="border-t border-slate-300 pt-8 mt-10 max-w-xs mx-auto reveal-text" style="animation-delay: 0.8s; opacity: 0;">
                        <p class="text-slate-500 text-sm font-medium uppercase tracking-widest">HR Information System</p>
                        <div class="flex items-center justify-center gap-2 mt-3">
                            <span class="text-slate-400 text-[10px] font-bold">V1.2.0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-6 sm:p-12 relative">
                <!-- Mobile Logo (shown only on small screens) -->
                <div class="lg:hidden text-center mb-8" x-show="show" x-transition:enter="transition ease-out duration-700 delay-300">
                    <div class="flex items-center justify-center mb-4">
                        <span class="text-5xl font-black text-gray-800">M</span>
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-300 to-blue-400 flex items-center justify-center border-2 border-yellow-400 mx-1">
                            <span class="text-lg font-bold text-gray-800">e</span>
                        </div>
                        <span class="text-5xl font-black text-gray-800">BS</span>
                    </div>
                </div>

                <div class="w-full sm:max-w-md bg-white/80 backdrop-blur-xl shadow-[0_20px_50px_rgba(0,0,0,0.1)] rounded-3xl overflow-hidden border border-white"
                     x-show="show" 
                     x-transition:enter="transition ease-out duration-700 delay-200"
                     x-transition:enter-start="opacity-0 scale-95 translate-x-10"
                     x-transition:enter-end="opacity-100 scale-100 translate-x-0">
                    <!-- Header -->
                    <div class="bg-blue-600 px-6 py-8 text-center relative overflow-hidden">
                        <div class="absolute top-0 right-0 -tralslate-y-1/2 translate-x-1/2 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                        <div class="relative w-20 h-20 bg-white/10 backdrop-blur-md rounded-2xl mx-auto mb-4 flex items-center justify-center border border-white/20 shadow-inner group">
                            <svg class="w-10 h-10 text-white transition-transform duration-500 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-3xl font-bold text-white tracking-tight">Welcome Back</h3>
                        <p class="text-blue-100 mt-2 text-sm font-medium opacity-80">Access your portal securely</p>
                    </div>
                    
                    <!-- Form Content -->
                    <div class="px-8 py-8">
                        {{ $slot }}
                    </div>
                </div>

                <p class="text-slate-400 text-[10px] font-bold mt-8 text-center uppercase tracking-widest"
                   x-show="show" x-transition:enter="transition ease-out duration-700 delay-500">
                    © {{ date('Y') }} Mancao E-connect Business Solutions • PH
                </p>
            </div>
        </div>
    </body>
</html>
