<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" x-data="{ loading: false }" @submit="loading = true">
        @csrf

        <!-- Email Address -->
        <div class="mb-5">
            <label for="email" class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Email Address</label>
            <div class="relative group">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 group-focus-within:text-blue-500 transition-colors pointer-events-none">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="w-full pl-12 pr-4 py-4 bg-gray-50/50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all outline-none text-sm font-bold"
                       placeholder="name@mebs.com">
            </div>
            @error('email')
                <p class="mt-1.5 text-[11px] font-bold text-red-500 uppercase tracking-tight">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-5" x-data="{ show: false }">
            <div class="flex justify-between items-center mb-2 px-1">
                <label for="password" class="block text-[11px] font-black text-gray-400 uppercase tracking-widest">Password</label>
                <span class="text-[10px] font-black text-blue-600/50 uppercase tracking-widest cursor-help" title="Please contact your HR or System Admin to reset your password.">
                    Contact Admin?
                </span>
            </div>
            <div class="relative group">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 group-focus-within:text-blue-500 transition-colors pointer-events-none">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </span>
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                       class="w-full pl-12 pr-12 py-4 bg-gray-50/50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all outline-none text-sm font-bold"
                       placeholder="••••••••">
                
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-blue-600 transition-colors">
                    <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    <svg x-show="show" style="display: none;" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.05 10.05 0 014.13-5.22m3.058-1.735A10.02 10.02 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.215m-4.13-4.13l1.414 1.414m-1.414 0L12 12.828l-1.414-1.414m0 0L9.172 10.828l-1.414 1.414m0 0L10.586 12l-1.414 1.414" /></svg>
                </button>
            </div>
            @error('password')
                <p class="mt-1.5 text-[11px] font-bold text-red-500 uppercase tracking-tight">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="flex items-center mb-8 px-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                <input id="remember_me" type="checkbox" name="remember" class="w-5 h-5 rounded-lg border-gray-200 text-blue-600 focus:ring-4 focus:ring-blue-500/10 transition custom-checkbox">
                <span class="ml-3 text-xs font-bold text-gray-500 group-hover:text-gray-700 transition-colors uppercase tracking-widest">Keep me signed in</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-center">
            <button type="submit" :disabled="loading"
                    class="group w-full py-4 px-6 text-white font-black rounded-[1.5rem] shadow-xl transition-all duration-300 bg-gradient-to-br from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 transform hover:-translate-y-1 hover:shadow-blue-200 disabled:opacity-70 disabled:cursor-not-allowed">
                <span class="flex items-center justify-center gap-3">
                    <span class="text-lg uppercase tracking-widest" x-text="loading ? 'Authenticating...' : 'Sign In'">Sign In</span>
                    <svg x-show="!loading" class="w-6 h-6 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                    <svg x-show="loading" style="display: none;" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
        </div>
    </form>
</x-guest-layout>
