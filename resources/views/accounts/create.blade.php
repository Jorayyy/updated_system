<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $type == 'role' ? __('Add New User Role') : __('Add New Campaign Account') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    <form method="POST" action="{{ route('accounts.store') }}">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">
                        
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-bold text-gray-700 uppercase tracking-tight mb-1">
                                    {{ $type == 'role' ? 'Role Name *' : 'Campaign / Account Name *' }}
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                    placeholder="{{ $type == 'role' ? 'e.g. CEBU Site Manager' : 'e.g. Amazon Campaign' }}"
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition">
                                @error('name') <p class="mt-1 text-xs text-red-600 font-bold uppercase">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="site_id" class="block text-sm font-bold text-gray-700 uppercase tracking-tight mb-1">Assigned Site (Optional)</label>
                                <select name="site_id" id="site_id" 
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition">
                                    <option value="">Global / No Site</option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site->id }}" {{ old('site_id') == $site->id ? 'selected' : '' }}>
                                            {{ $site->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-[10px] text-gray-400 italic">Restrict this {{ $type }} to a specific branch location</p>
                                @error('site_id') <p class="mt-1 text-xs text-red-600 font-bold uppercase">{{ $message }}</p> @enderror
                            </div>

                            @if($type == 'role')
                                <div>
                                    <label for="hierarchy_level" class="block text-sm font-bold text-gray-700 uppercase tracking-tight mb-1">Hierarchy Score (0-100) *</label>
                                    <input type="number" name="hierarchy_level" id="hierarchy_level" value="{{ old('hierarchy_level', 0) }}" required min="0" max="100"
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition">
                                    <p class="mt-1 text-[10px] text-gray-400 italic font-medium uppercase tracking-tighter">
                                        Prevents lower levels from editing higher ones. (100=Super Admin, 0=Standard Employee)
                                    </p>
                                    @error('hierarchy_level') <p class="mt-1 text-xs text-red-600 font-bold uppercase">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="system_role" class="block text-sm font-bold text-gray-700 uppercase tracking-tight mb-1">Access Permissions Category *</label>
                                    <select name="system_role" id="system_role" required
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition">
                                        <option value="employee" {{ old('system_role') == 'employee' ? 'selected' : '' }}>Employee (Standard Personnel)</option>
                                        <option value="accounting" {{ old('system_role') == 'accounting' ? 'selected' : '' }}>Accounting (Payroll & Finance)</option>
                                        <option value="hr" {{ old('system_role') == 'hr' ? 'selected' : '' }}>HR (People Management)</option>
                                        <option value="admin" {{ old('system_role') == 'admin' ? 'selected' : '' }}>Admin (Full Site Control)</option>
                                        @if(auth()->user()->isSuperAdmin())
                                            <option value="super_admin" {{ old('system_role') == 'super_admin' ? 'selected' : '' }}>Super Admin (System Owner)</option>
                                        @endif
                                    </select>
                                    <p class="mt-1 text-[10px] text-gray-400 italic">This defines the menus and dashboard permissions for users under this {{ $type }}.</p>
                                    @error('system_role') <p class="mt-1 text-xs text-red-600 font-bold uppercase">{{ $message }}</p> @enderror
                                </div>
                            @else
                                <input type="hidden" name="hierarchy_level" value="0">
                                <input type="hidden" name="system_role" value="employee">
                            @endif

                            <div>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 transition">
                                    <span class="text-sm font-bold text-gray-700 uppercase tracking-tight group-hover:text-indigo-600 transition">Currently Active</span>
                                </label>
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-bold text-gray-700 uppercase tracking-tight mb-1">Description / Notes</label>
                                <textarea name="description" id="description" rows="3"
                                    placeholder="Briefly describe the purpose of this {{ $type }}..."
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition">{{ old('description') }}</textarea>
                                @error('description') <p class="mt-1 text-xs text-red-600 font-bold uppercase">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-100">
                            <a href="{{ route('accounts.index', ['type' => $type]) }}" class="bg-gray-100 text-gray-600 px-5 py-2.5 rounded-lg hover:bg-gray-200 font-bold uppercase text-xs tracking-widest transition">Cancel</a>
                            <button type="submit" class="bg-indigo-600 text-white px-8 py-2.5 rounded-lg hover:bg-indigo-700 font-bold shadow-lg hover:shadow-xl uppercase text-xs tracking-widest transition transform hover:-translate-y-0.5">
                                {{ $type == 'role' ? 'Create User Role' : 'Create Campaign' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
