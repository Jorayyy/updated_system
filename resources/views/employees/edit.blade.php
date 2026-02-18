<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit 201 File') }}: <span class="text-indigo-600">{{ $employee->name }}</span>
            </h2>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-widest text-gray-400">Status:</span>
                @if($employee->is_active)
                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-[10px] font-bold uppercase">Active</span>
                @else
                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-[10px] font-bold uppercase">Inactive</span>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900">
                    <form method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Profile Photo Section -->
                        <div class="mb-10 flex flex-col items-center">
                            <div class="relative group">
                                <div class="w-32 h-32 rounded-full overflow-hidden bg-slate-100 border-4 border-indigo-50 shadow-lg">
                                    <img id="preview" src="{{ $employee->profile_photo ? asset('storage/' . $employee->profile_photo) : 'https://ui-avatars.com/api/?name=' . urlencode($employee->name) . '&background=6366f1&color=fff&size=128' }}" 
                                         class="w-full h-full object-cover" alt="Profile Preview">
                                </div>
                                <label for="profile_photo" class="absolute bottom-0 right-0 bg-indigo-600 text-white p-2 rounded-full cursor-pointer shadow-lg hover:bg-indigo-700 transition transform hover:scale-110">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <input type="file" name="profile_photo" id="profile_photo" class="hidden" accept="image/*" onchange="previewImage(this)">
                                </label>
                            </div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-4">201 File Profile Picture</p>
                            @error('profile_photo')
                                <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Section 1: Personal Information -->
                        <div class="mb-12">
                            <div class="flex items-center mb-6">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-bold mr-3">1</span>
                                <h3 class="text-xl font-bold text-gray-800 uppercase tracking-tight">Personal Information</h3>
                                <div class="ml-4 flex-grow border-t border-gray-200"></div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Title</label>
                                    <select name="title" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">None</option>
                                        <option value="Mr." {{ old('title', $employee->title) == 'Mr.' ? 'selected' : '' }}>Mr.</option>
                                        <option value="Ms." {{ old('title', $employee->title) == 'Ms.' ? 'selected' : '' }}>Ms.</option>
                                        <option value="Mrs." {{ old('title', $employee->title) == 'Mrs.' ? 'selected' : '' }}>Mrs.</option>
                                        <option value="Dr." {{ old('title', $employee->title) == 'Dr.' ? 'selected' : '' }}>Dr.</option>
                                    </select>
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">First Name *</label>
                                    <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}" required
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('first_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Middle Name</label>
                                    <input type="text" name="middle_name" value="{{ old('middle_name', $employee->middle_name) }}"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Last Name *</label>
                                    <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}" required
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('last_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Extension (Jr/Sr)</label>
                                    <input type="text" name="name_extension" value="{{ old('name_extension', $employee->name_extension) }}" placeholder="e.g. Jr."
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Birthday *</label>
                                    <input type="date" name="birthday" value="{{ old('birthday', $employee->birthday?->format('Y-m-d')) }}" required
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('birthday')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Gender *</label>
                                    <select name="gender" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select Gender</option>
                                        <option value="Male" {{ old('gender', $employee->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender', $employee->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                    @error('gender')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Civil Status *</label>
                                    <select name="civil_status" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select Status</option>
                                        <option value="Single" {{ old('civil_status', $employee->civil_status) == 'Single' ? 'selected' : '' }}>Single</option>
                                        <option value="Married" {{ old('civil_status', $employee->civil_status) == 'Married' ? 'selected' : '' }}>Married</option>
                                        <option value="Widowed" {{ old('civil_status', $employee->civil_status) == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                        <option value="Separated" {{ old('civil_status', $employee->civil_status) == 'Separated' ? 'selected' : '' }}>Separated</option>
                                    </select>
                                    @error('civil_status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Citizenship</label>
                                    <input type="text" name="citizenship" value="{{ old('citizenship', $employee->citizenship ?? 'Filipino') }}"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Religion</label>
                                    <input type="text" name="religion" value="{{ old('religion', $employee->religion) }}"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Blood Type</label>
                                    <input type="text" name="blood_type" value="{{ old('blood_type', $employee->blood_type) }}" placeholder="e.g. O+"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Place of Birth</label>
                                    <input type="text" name="place_of_birth" value="{{ old('place_of_birth', $employee->place_of_birth) }}"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Employment Information -->
                        <div class="mb-12 border-t border-gray-100 pt-12">
                            <div class="flex items-center mb-6">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-bold mr-3">2</span>
                                <h3 class="text-xl font-bold text-gray-800 uppercase tracking-tight">Employment Information</h3>
                                <div class="ml-4 flex-grow border-t border-gray-200"></div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Employee ID *</label>
                                    <input type="text" name="employee_id" value="{{ old('employee_id', $employee->employee_id) }}" required
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 font-mono font-bold">
                                    @error('employee_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Employment Type *</label>
                                    <select name="employment_type" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="Regular" {{ old('employment_type', $employee->employment_type) == 'Regular' ? 'selected' : '' }}>Regular</option>
                                        <option value="Probationary" {{ old('employment_type', $employee->employment_type) == 'Probationary' ? 'selected' : '' }}>Probationary</option>
                                        <option value="Contractual" {{ old('employment_type', $employee->employment_type) == 'Contractual' ? 'selected' : '' }}>Contractual</option>
                                        <option value="Project-based" {{ old('employment_type', $employee->employment_type) == 'Project-based' ? 'selected' : '' }}>Project-based</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Classification *</label>
                                    <select name="classification" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="Rank and File" {{ old('classification', $employee->classification) == 'Rank and File' ? 'selected' : '' }}>Rank and File</option>
                                        <option value="Supervisory" {{ old('classification', $employee->classification) == 'Supervisory' ? 'selected' : '' }}>Supervisory</option>
                                        <option value="Managerial" {{ old('classification', $employee->classification) == 'Managerial' ? 'selected' : '' }}>Managerial</option>
                                        <option value="Executive" {{ old('classification', $employee->classification) == 'Executive' ? 'selected' : '' }}>Executive</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Tax Code *</label>
                                    <input type="text" name="tax_code" value="{{ old('tax_code', $employee->tax_code ?? 'M') }}" required
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Pay Type *</label>
                                    <select name="pay_type" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="Weekly" {{ old('pay_type', $employee->pay_type) == 'Weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="Bi-Weekly" {{ old('pay_type', $employee->pay_type) == 'Bi-Weekly' ? 'selected' : '' }}>Bi-Weekly</option>
                                        <option value="Monthly" {{ old('pay_type', $employee->pay_type) == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Payroll Cycle *</label>
                                    <select name="payroll_group_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select Cycle</option>
                                        @foreach($payrollGroups as $group)
                                            <option value="{{ $group->id }}" {{ old('payroll_group_id', $employee->payroll_group_id) == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('payroll_group_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Department *</label>
                                    <select name="department_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select Department</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Position *</label>
                                    <input type="text" name="position" value="{{ old('position', $employee->position) }}" required
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Site *</label>
                                    <select name="site_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select Site</option>
                                        @foreach($sites as $site)
                                            <option value="{{ $site->id }}" {{ old('site_id', $employee->site_id) == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Date Hired *</label>
                                    <input type="date" name="date_hired" value="{{ old('date_hired', $employee->date_hired?->format('Y-m-d')) }}" required
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('date_hired')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Status *</label>
                                    <select name="is_active" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="1" {{ old('is_active', $employee->is_active) == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('is_active', $employee->is_active) == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Campaign Account *</label>
                                    <select name="account_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select Account</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}" {{ old('account_id', $employee->account_id) == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('account_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Reports To (Superior)</label>
                                    <select name="report_to" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">None (Top Level)</option>
                                        @foreach($employees as $sup)
                                            @if($sup->id !== $employee->id) {{-- Prevent reporting to self --}}
                                                <option value="{{ $sup->id }}" {{ old('report_to', $employee->report_to) == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: System Access & Role -->
                        <div class="mb-12 p-6 bg-slate-50 border border-slate-200 rounded-xl">
                            <div class="flex items-center mb-6">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-600 text-white font-bold mr-3">3</span>
                                <h3 class="text-xl font-bold text-gray-800 uppercase tracking-tight">System Account Settings</h3>
                                <div class="ml-4 flex-grow border-t border-slate-200"></div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-bold text-indigo-900 uppercase tracking-wide mb-1">System Email (Login) *</label>
                                    <input type="email" name="email" value="{{ old('email', $employee->email) }}" required
                                        class="w-full border-indigo-200 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('email')<p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>@enderror
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-bold text-indigo-900 uppercase tracking-wide mb-1">System Access Role *</label>
                                    <select name="role" required class="w-full border-indigo-200 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                                        <option value="employee" {{ old('role', $employee->role) == 'employee' ? 'selected' : '' }}>Employee (Standard)</option>
                                        <option value="hr" {{ old('role', $employee->role) == 'hr' ? 'selected' : '' }}>HR (Personnel Mgmt)</option>
                                        <option value="accounting" {{ old('role', $employee->role) == 'accounting' ? 'selected' : '' }}>Accounting (Payroll)</option>
                                        <option value="admin" {{ old('role', $employee->role) == 'admin' ? 'selected' : '' }}>Admin (Full Control)</option>
                                        @if(auth()->user()->isSuperAdmin())
                                            <option value="super_admin" {{ old('role', $employee->role) == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                        @endif
                                    </select>
                                    <p class="mt-1 text-[10px] text-gray-500 italic uppercase font-bold tracking-tighter">Determines system permissions and menu access.</p>
                                    @error('role')<p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>@enderror
                                </div>
                                <div></div>
                                <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-white border border-indigo-100 rounded-lg">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">New Password (Optional)</label>
                                        <input type="password" name="password" placeholder="Leave blank to keep current"
                                            class="w-full border-gray-100 bg-slate-50 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Confirm New Password</label>
                                        <input type="password" name="password_confirmation" placeholder="Verify new password"
                                            class="w-full border-gray-100 bg-slate-50 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Banking & Government IDs -->
                        <div class="mb-12 border-t border-gray-100 pt-12">
                            <div class="flex items-center mb-6">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-bold mr-3">4</span>
                                <h3 class="text-xl font-bold text-gray-800 uppercase tracking-tight">Banking & Government IDs</h3>
                                <div class="ml-4 flex-grow border-t border-gray-200"></div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Bank Name</label>
                                    <input type="text" name="bank" value="{{ old('bank', $employee->bank) }}" placeholder="e.g. BDO, BPI"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Account Number</label>
                                    <input type="text" name="account_no" value="{{ old('account_no', $employee->account_no) }}"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">TIN</label>
                                    <input type="text" name="tin" value="{{ old('tin', $employee->tin) }}" placeholder="000-000-000-000"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">SSS Number</label>
                                    <input type="text" name="sss_number" value="{{ old('sss_number', $employee->sss_number) }}"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">PhilHealth Number</label>
                                    <input type="text" name="philhealth_number" value="{{ old('philhealth_number', $employee->philhealth_number) }}"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Pag-IBIG Number</label>
                                    <input type="text" name="pagibig_number" value="{{ old('pagibig_number', $employee->pagibig_number) }}"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>

                        <!-- Section 5: Contact & Social Information -->
                        <div class="mb-12 border-t border-gray-100 pt-12">
                            <div class="flex items-center mb-6">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-bold mr-3">5</span>
                                <h3 class="text-xl font-bold text-gray-800 uppercase tracking-tight">Contact Information</h3>
                                <div class="ml-4 flex-grow border-t border-gray-200"></div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Mobile No. 1</label>
                                    <input type="text" name="mobile_no_1" value="{{ old('mobile_no_1', $employee->mobile_no_1) }}"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Mobile No. 2</label>
                                    <input type="text" name="mobile_no_2" value="{{ old('mobile_no_2', $employee->mobile_no_2) }}"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Instagram</label>
                                    <input type="text" name="instagram" value="{{ old('instagram', $employee->instagram) }}" placeholder="Username"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">Facebook Profile</label>
                                    <input type="text" name="facebook" value="{{ old('facebook', $employee->facebook) }}" placeholder="URL or Username"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>

                        <!-- Section 6: Address Details -->
                        <div class="mb-12 border-t border-gray-100 pt-12">
                            <div class="flex items-center mb-6">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-bold mr-3">6</span>
                                <h3 class="text-xl font-bold text-gray-800 uppercase tracking-tight">Address Information</h3>
                                <div class="ml-4 flex-grow border-t border-gray-200"></div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                                <!-- Permanent -->
                                <div class="space-y-4 p-5 bg-indigo-50/30 rounded-xl border border-indigo-100">
                                    <h4 class="font-bold text-indigo-900 border-b border-indigo-100 pb-2 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                        Permanent Address
                                    </h4>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Street / Barangay / City *</label>
                                        <textarea name="permanent_address" rows="2" required
                                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('permanent_address', $employee->permanent_address) }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Province *</label>
                                        <input type="text" name="permanent_province" value="{{ old('permanent_province', $employee->permanent_province) }}" required
                                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                                <!-- Present -->
                                <div class="space-y-4 p-5 bg-indigo-50/30 rounded-xl border border-indigo-100">
                                    <h4 class="font-bold text-indigo-900 border-b border-indigo-100 pb-2 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Present Address
                                    </h4>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Street / Barangay / City *</label>
                                        <textarea name="present_address" rows="2" required
                                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('present_address', $employee->present_address) }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Province *</label>
                                        <input type="text" name="present_province" value="{{ old('present_province', $employee->present_province) }}" required
                                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 7: Other Info -->
                        <div class="mb-12 border-t border-gray-100 pt-12">
                            <div class="flex items-center mb-6">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-bold mr-3">7</span>
                                <h3 class="text-xl font-bold text-gray-800 uppercase tracking-tight">Additional Information</h3>
                                <div class="ml-4 flex-grow border-t border-gray-200"></div>
                            </div>
                            <div>
                                <textarea name="other_info" rows="3" placeholder="Any additional notes..."
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('other_info', $employee->other_info) }}</textarea>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-4 pt-8 border-t border-gray-100">
                            <a href="{{ route('employees.index') }}" 
                               class="px-6 py-2.5 rounded-lg text-sm font-bold text-gray-500 uppercase tracking-widest hover:bg-gray-100 transition">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="px-8 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-bold uppercase tracking-widest shadow-lg hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 transition transform hover:-translate-y-0.5">
                                Save Changes
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

