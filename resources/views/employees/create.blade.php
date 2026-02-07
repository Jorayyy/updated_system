<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Employee') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Profile Photo -->
                        <div class="mb-6 flex flex-col items-center">
                            <div class="relative group">
                                <div class="w-24 h-24 rounded-full overflow-hidden bg-slate-100 border-4 border-white shadow-md">
                                    <img id="preview" src="https://ui-avatars.com/api/?name=New+User&background=6366f1&color=fff&size=128" 
                                         class="w-full h-full object-cover" alt="Profile Preview">
                                </div>
                                <label for="profile_photo" class="absolute bottom-0 right-0 bg-indigo-600 text-white p-1.5 rounded-full cursor-pointer shadow-lg hover:bg-indigo-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <input type="file" name="profile_photo" id="profile_photo" class="hidden" accept="image/*" onchange="previewImage(this)">
                                </label>
                            </div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-3">Upload Profile Picture</p>
                            @error('profile_photo')
                                <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Employee ID -->
                            <div>
                                <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee ID *</label>
                                <input type="text" name="employee_id" id="employee_id" value="{{ old('employee_id') }}" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('employee_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- System Access Level -->
                            <div class="md:col-span-1 bg-red-50 p-4 rounded-lg border border-red-100">
                                <label for="role" class="block text-sm font-bold text-red-900 uppercase tracking-tight">System Access Level *</label>
                                <select name="role" id="role" required
                                    class="mt-2 block w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500 bg-white">
                                    <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>Employee (Standard)</option>
                                    <option value="accounting" {{ old('role') == 'accounting' ? 'selected' : '' }}>Accounting</option>
                                    <option value="hr" {{ old('role') == 'hr' ? 'selected' : '' }}>HR Manager</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                                    @if(auth()->user()->isSuperAdmin())
                                        <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                    @endif
                                </select>
                                <p class="mt-2 text-[10px] text-red-700 italic">
                                    Determines what pages this user can see.
                                </p>
                                @error('role')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Campaign Assignment (Work Group) -->
                            <div class="md:col-span-1 bg-blue-50 p-4 rounded-lg border border-blue-100">
                                <label for="campaign_id" class="block text-sm font-bold text-blue-900 uppercase tracking-tight">Campaign / Client</label>
                                <select name="campaign_id" id="campaign_id" required
                                    class="mt-2 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-white font-bold text-blue-600 uppercase">
                                    <option value="">Select Campaign...</option>
                                    @foreach($campaigns as $campaign)
                                        <option value="{{ $campaign->id }}" {{ old('campaign_id') == $campaign->id ? 'selected' : '' }}>
                                            {{ $campaign->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-[10px] text-blue-700 italic">
                                    The client project this employee belongs to.
                                </p>
                                @error('campaign_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Job Role / Designation -->
                            <div class="md:col-span-1 bg-green-50 p-4 rounded-lg border border-green-100">
                                <label for="designation_id" class="block text-sm font-bold text-green-900 uppercase tracking-tight">Job Role / Designation *</label>
                                <select name="designation_id" id="designation_id" required
                                    class="mt-2 block w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 bg-white">
                                    <option value="">Select Designation...</option>
                                    @foreach($designations as $designation)
                                        <option value="{{ $designation->id }}" {{ old('designation_id') == $designation->id ? 'selected' : '' }}>
                                            {{ $designation->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-[10px] text-green-700 italic">
                                    The actual profession/title of the employee.
                                </p>
                                @error('designation_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Site -->
                            <div>
                                <label for="site_id" class="block text-sm font-medium text-gray-700">Site Location *</label>
                                <select name="site_id" id="site_id" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select Site</option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site->id }}" {{ old('site_id') == $site->id ? 'selected' : '' }}>
                                            {{ $site->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('site_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Removed redundant Department and Position fields -->

                            <!-- Date Hired -->
                            <div>
                                <label for="date_hired" class="block text-sm font-medium text-gray-700">Date Hired</label>
                                <input type="date" name="date_hired" id="date_hired" value="{{ old('date_hired') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Monthly Salary -->
                            <div>
                                <label for="monthly_salary" class="block text-sm font-medium text-gray-700">Monthly Salary (₱)</label>
                                <input type="number" step="0.01" name="monthly_salary" id="monthly_salary" value="{{ old('monthly_salary', 0) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Daily Rate -->
                            <div>
                                <label for="daily_rate" class="block text-sm font-medium text-gray-700">Daily Rate (₱)</label>
                                <input type="number" step="0.01" name="daily_rate" id="daily_rate" value="{{ old('daily_rate', 0) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Hourly Rate -->
                            <div>
                                <label for="hourly_rate" class="block text-sm font-medium text-gray-700">Hourly Rate (₱)</label>
                                <input type="number" step="0.01" name="hourly_rate" id="hourly_rate" value="{{ old('hourly_rate', 0) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Password *</label>
                                <input type="password" name="password" id="password" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password *</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-4">
                            <a href="{{ route('employees.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                                Cancel
                            </a>
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                Create Employee
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
