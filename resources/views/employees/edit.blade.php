<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Employee') }} - {{ $employee->name }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Profile Photo -->
                        <div class="mb-6 flex flex-col items-center">
                            <div class="relative group">
                                <div class="w-24 h-24 rounded-full overflow-hidden bg-slate-100 border-4 border-white shadow-md">
                                    <img id="preview" src="{{ $employee->profile_photo ? asset('storage/' . $employee->profile_photo) : 'https://ui-avatars.com/api/?name=' . urlencode($employee->name) . '&background=6366f1&color=fff&size=128' }}" 
                                         class="w-full h-full object-cover" alt="Profile Preview">
                                </div>
                                <label for="profile_photo" class="absolute bottom-0 right-0 bg-indigo-600 text-white p-1.5 rounded-full cursor-pointer shadow-lg hover:bg-indigo-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <input type="file" name="profile_photo" id="profile_photo" class="hidden" accept="image/*" onchange="previewImage(this)">
                                </label>
                            </div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-3">Update Profile Picture</p>
                            @error('profile_photo')
                                <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Employee ID -->
                            <div>
                                <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee ID *</label>
                                <input type="text" name="employee_id" id="employee_id" value="{{ old('employee_id', $employee->employee_id) }}" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('employee_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $employee->name) }}" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $employee->email) }}" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- User Role (Permissions) -->
                            <div class="md:col-span-2 bg-indigo-50 p-4 rounded-lg border border-indigo-100">
                                <label for="account_id" class="block text-sm font-bold text-indigo-900 uppercase tracking-tight">Assigned User Role (System Permissions) *</label>
                                <select name="account_id" id="account_id" required
                                    class="mt-2 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                                    <option value="">Select Role...</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" {{ old('account_id', $employee->account_id) == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }} @if($account->site) — [{{ $account->site->name }}] @endif (Level: {{ $account->hierarchy_level }}, Type: {{ ucfirst($account->system_role) }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-xs text-indigo-700 italic">
                                    <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                                    The chosen User Role determines which menus and features this employee can access.
                                </p>
                                @error('account_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Site -->
                            <div>
                                <label for="site_id" class="block text-sm font-medium text-gray-700">Site</label>
                                <select name="site_id" id="site_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select Site</option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site->id }}" {{ old('site_id', $employee->site_id) == $site->id ? 'selected' : '' }}>
                                            {{ $site->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('site_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Department -->
                            <div>
                                <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                                <select name="department_id" id="department_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Position -->
                            <div>
                                <label for="position" class="block text-sm font-medium text-gray-700">Position</label>
                                <input type="text" name="position" id="position" value="{{ old('position', $employee->position) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Date Hired -->
                            <div>
                                <label for="date_hired" class="block text-sm font-medium text-gray-700">Date Hired</label>
                                <input type="date" name="date_hired" id="date_hired" value="{{ old('date_hired', $employee->date_hired?->format('Y-m-d')) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="is_active" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="is_active" id="is_active"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="1" {{ old('is_active', $employee->is_active) ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ !old('is_active', $employee->is_active) ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <!-- SSS Number -->
                            <div>
                                <label for="sss_number" class="block text-sm font-medium text-gray-700">SSS Number</label>
                                <input type="text" name="sss_number" id="sss_number" value="{{ old('sss_number', $employee->sss_number) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('sss_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- PhilHealth Number -->
                            <div>
                                <label for="philhealth_number" class="block text-sm font-medium text-gray-700">PhilHealth Number</label>
                                <input type="text" name="philhealth_number" id="philhealth_number" value="{{ old('philhealth_number', $employee->philhealth_number) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('philhealth_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Pag-IBIG Number -->
                            <div>
                                <label for="pagibig_number" class="block text-sm font-medium text-gray-700">Pag-IBIG Number</label>
                                <input type="text" name="pagibig_number" id="pagibig_number" value="{{ old('pagibig_number', $employee->pagibig_number) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('pagibig_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Salary & Payroll Structure (Production-Ready Philippine Standard) -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest mb-6 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Salary & Payroll Structure
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Base Pay -->
                                <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                                    <div>
                                        <label for="monthly_salary" class="block text-xs font-bold text-slate-500 uppercase">Monthly Salary (₱)</label>
                                        <input type="number" step="0.01" name="monthly_salary" id="monthly_salary" value="{{ old('monthly_salary', $employee->monthly_salary) }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono">
                                    </div>
                                    <div>
                                        <label for="daily_rate" class="block text-xs font-bold text-slate-500 uppercase">Daily Rate (₱)</label>
                                        <input type="number" step="0.01" name="daily_rate" id="daily_rate" value="{{ old('daily_rate', $employee->daily_rate) }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono">
                                    </div>
                                    <div>
                                        <label for="hourly_rate" class="block text-xs font-bold text-slate-500 uppercase">Hourly Rate (₱)</label>
                                        <input type="number" step="0.01" name="hourly_rate" id="hourly_rate" value="{{ old('hourly_rate', $employee->hourly_rate) }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono">
                                    </div>
                                </div>

                                <!-- Bonuses (Modular) -->
                                <div class="md:col-span-3 mt-4">
                                    <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest mb-2 px-1">Modular Bonuses & Incentives (Philippine Standard)</p>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label for="perfect_attendance_bonus" class="block text-xs font-medium text-gray-700">Perfect Attend. (Flat)</label>
                                            <input type="number" step="0.01" name="perfect_attendance_bonus" id="perfect_attendance_bonus" value="{{ old('perfect_attendance_bonus', $employee->perfect_attendance_bonus) }}"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                                        </div>
                                        <div>
                                            <label for="attendance_incentive" class="block text-xs font-medium text-gray-700">Attend. Incent. (Daily)</label>
                                            <input type="number" step="0.01" name="attendance_incentive" id="attendance_incentive" value="{{ old('attendance_incentive', $employee->attendance_incentive) }}"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                                        </div>
                                        <div>
                                            <label for="site_incentive" class="block text-xs font-medium text-gray-700">Site Incentive (Daily)</label>
                                            <input type="number" step="0.01" name="site_incentive" id="site_incentive" value="{{ old('site_incentive', $employee->site_incentive) }}"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                                        </div>
                                        <div>
                                            <label for="cola" class="block text-xs font-medium text-gray-700">COLA (Daily)</label>
                                            <input type="number" step="0.01" name="cola" id="cola" value="{{ old('cola', $employee->cola) }}"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                                        </div>
                                        <div>
                                            <label for="other_allowance" class="block text-xs font-medium text-gray-700">Other Allowance (Flat)</label>
                                            <input type="number" step="0.01" name="other_allowance" id="other_allowance" value="{{ old('other_allowance', $employee->other_allowance) }}"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                                        </div>
                                    </div>
                                </div>

                                <!-- Standard Allowances -->
                                <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                    <div>
                                        <label for="meal_allowance" class="block text-xs font-medium text-gray-700">Meal Allowance (₱)</label>
                                        <input type="number" step="0.01" name="meal_allowance" id="meal_allowance" value="{{ old('meal_allowance', $employee->meal_allowance) }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div>
                                        <label for="transportation_allowance" class="block text-xs font-medium text-gray-700">Transpo Allowance (₱)</label>
                                        <input type="number" step="0.01" name="transportation_allowance" id="transportation_allowance" value="{{ old('transportation_allowance', $employee->transportation_allowance) }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div>
                                        <label for="communication_allowance" class="block text-xs font-medium text-gray-700">Comm Allowance (₱)</label>
                                        <input type="number" step="0.01" name="communication_allowance" id="communication_allowance" value="{{ old('communication_allowance', $employee->communication_allowance) }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end gap-4 pt-6 border-t border-gray-100">
                            <a href="{{ route('employees.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                                Cancel
                            </a>
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                Update Employee
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</x-app-layout>
