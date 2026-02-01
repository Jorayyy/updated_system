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

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Role Selection -->
        <div class="mb-5">
            <label class="block text-sm font-semibold text-gray-700 mb-3">Login As</label>
            <div class="grid grid-cols-3 gap-2">
                <!-- Employee -->
                <div class="role-option cursor-pointer" data-role="employee">
                    <input type="radio" name="role" value="employee" class="hidden" {{ old('role', 'employee') === 'employee' ? 'checked' : '' }}>
                    <div class="p-3 text-center border-2 rounded-lg transition-all border-gray-300 hover:border-blue-400">
                        <svg class="w-6 h-6 mx-auto mb-1 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="text-xs font-medium text-gray-700">Employee</span>
                    </div>
                </div>

                <!-- HR -->
                <div class="role-option cursor-pointer" data-role="hr">
                    <input type="radio" name="role" value="hr" class="hidden" {{ old('role') === 'hr' ? 'checked' : '' }}>
                    <div class="p-3 text-center border-2 rounded-lg transition-all border-gray-300 hover:border-green-400">
                        <svg class="w-6 h-6 mx-auto mb-1 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span class="text-xs font-medium text-gray-700">HR</span>
                    </div>
                </div>

                <!-- Admin -->
                <div class="role-option cursor-pointer" data-role="admin">
                    <input type="radio" name="role" value="admin" class="hidden" {{ old('role') === 'admin' ? 'checked' : '' }}>
                    <div class="p-3 text-center border-2 rounded-lg transition-all border-gray-300 hover:border-red-400">
                        <svg class="w-6 h-6 mx-auto mb-1 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <span class="text-xs font-medium text-gray-700">Admin</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Address -->
        <div class="mb-4">
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                   placeholder="Enter your email">
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                   placeholder="Enter your password">
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between mb-6">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-600">Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-blue-600 hover:text-blue-800 font-medium" href="{{ route('password.request') }}">
                    Forgot password?
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <button type="submit" id="loginBtn" 
                class="w-full py-3 px-4 text-white font-semibold rounded-lg shadow-lg transition-all duration-200 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800" style="background: linear-gradient(to right, #2563eb, #1d4ed8) !important;">
            <span class="flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                </svg>
                <span id="loginBtnText">Sign In as Employee</span>
            </span>
        </button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleOptions = document.querySelectorAll('.role-option');
            const loginBtn = document.getElementById('loginBtn');
            const loginBtnText = document.getElementById('loginBtnText');
            
            function updateButtonStyle(role) {
                const styles = {
                    employee: { 
                        class: 'w-full py-3 px-4 text-white font-semibold rounded-lg shadow-lg transition-all duration-200',
                        style: 'background: linear-gradient(to right, #2563eb, #1d4ed8) !important;'
                    },
                    hr: { 
                        class: 'w-full py-3 px-4 text-white font-semibold rounded-lg shadow-lg transition-all duration-200',
                        style: 'background: linear-gradient(to right, #16a34a, #15803d) !important;'
                    },
                    admin: { 
                        class: 'w-full py-3 px-4 text-white font-semibold rounded-lg shadow-lg transition-all duration-200',
                        style: 'background: linear-gradient(to right, #dc2626, #b91c1c) !important;'
                    }
                };
                
                loginBtn.className = styles[role].class;
                loginBtn.style.cssText = styles[role].style;
                loginBtnText.textContent = 'Sign In as ' + role.charAt(0).toUpperCase() + role.slice(1);
            }
            
            roleOptions.forEach(function(option) {
                option.addEventListener('click', function() {
                    const role = this.dataset.role;
                    const input = this.querySelector('input[type="radio"]');
                    const div = this.querySelector('div');
                    
                    // Clear all selections
                    roleOptions.forEach(function(opt) {
                        opt.querySelector('input[type="radio"]').checked = false;
                        const optDiv = opt.querySelector('div');
                        if (opt.dataset.role === 'employee') {
                            optDiv.className = 'p-3 text-center border-2 rounded-lg transition-all border-gray-300 hover:border-blue-400';
                        } else if (opt.dataset.role === 'hr') {
                            optDiv.className = 'p-3 text-center border-2 rounded-lg transition-all border-gray-300 hover:border-green-400';
                        } else if (opt.dataset.role === 'admin') {
                            optDiv.className = 'p-3 text-center border-2 rounded-lg transition-all border-gray-300 hover:border-red-400';
                        }
                    });
                    
                    // Select current option
                    input.checked = true;
                    
                    if (role === 'employee') {
                        div.className = 'p-3 text-center border-2 rounded-lg transition-all border-blue-500 bg-blue-50';
                    } else if (role === 'hr') {
                        div.className = 'p-3 text-center border-2 rounded-lg transition-all border-green-500 bg-green-50';
                    } else if (role === 'admin') {
                        div.className = 'p-3 text-center border-2 rounded-lg transition-all border-red-500 bg-red-50';
                    }
                    
                    updateButtonStyle(role);
                });
            });
            
            // Initialize with default selection
            const checkedInput = document.querySelector('input[name="role"]:checked');
            if (checkedInput) {
                const defaultRole = checkedInput.value;
                const defaultOption = document.querySelector('.role-option[data-role="' + defaultRole + '"]');
                if (defaultOption) {
                    defaultOption.click();
                }
            } else {
                // Default to employee
                document.querySelector('.role-option[data-role="employee"]').click();
            }
        });
    </script>
</x-guest-layout>
