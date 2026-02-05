<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New User Role') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('accounts.store') }}">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="site_id" class="block text-sm font-medium text-gray-700">Site (Optional)</label>
                                <select name="site_id" id="site_id" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Global / No Site</option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site->id }}" {{ old('site_id') == $site->id ? 'selected' : '' }}>
                                            {{ $site->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500 italic text-right">Restrict this role to a specific branch/location</p>
                                @error('site_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Role Name *</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                    placeholder="e.g. CEBU Site Manage"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="hierarchy_level" class="block text-sm font-medium text-gray-700">Hierarchy Level (0-100) *</label>
                                <input type="number" name="hierarchy_level" id="hierarchy_level" value="{{ old('hierarchy_level', 0) }}" required min="0" max="100"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <p class="mt-1 text-xs text-gray-500 italic">100 = Super Admin, 80 = Admin, 60 = HR, 40 = Accounting, 20 = Leader, 0 = Employee</p>
                                @error('hierarchy_level') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="system_role" class="block text-sm font-medium text-gray-700">System Permissions Category *</label>
                                <select name="system_role" id="system_role" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="employee" {{ old('system_role') == 'employee' ? 'selected' : '' }}>Employee (Standard)</option>
                                    <option value="accounting" {{ old('system_role') == 'accounting' ? 'selected' : '' }}>Accounting (Payroll Access)</option>
                                    <option value="hr" {{ old('system_role') == 'hr' ? 'selected' : '' }}>HR (Personnel Management)</option>
                                    <option value="admin" {{ old('system_role') == 'admin' ? 'selected' : '' }}>Admin (Full Management)</option>
                                    <option value="super_admin" {{ old('system_role') == 'super_admin' ? 'selected' : '' }}>Super Admin (System Owner)</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500 italic">This determines which menus and buttons the user can see.</p>
                                @error('system_role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Role Description</label>
                                <textarea name="description" id="description" rows="3"
                                    placeholder="Briefly describe the responsibilities..."
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-2">
                            <a href="{{ route('accounts.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 font-medium">Cancel</a>
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 font-bold transition">
                                Create User Role
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
