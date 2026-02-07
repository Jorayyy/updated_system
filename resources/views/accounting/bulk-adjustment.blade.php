<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Payroll Adjustments Management') }}
            </h2>
            <div class="flex gap-2">
                <button @click="$dispatch('open-modal', 'manual-adjustment-modal')" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors duration-200 text-sm font-bold flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Manual Add
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ activeTab: 'bonuses' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Dashboard / Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-blue-500">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Pending Bonuses</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $pendingBonuses->count() }} Items</p>
                    <p class="text-sm font-semibold text-blue-600">₱{{ number_format($pendingBonuses->sum('amount'), 2) }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-red-500">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Pending Deductions</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $pendingDeductions->count() }} Items</p>
                    <p class="text-sm font-semibold text-red-600">₱{{ number_format($pendingDeductions->sum('amount'), 2) }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-indigo-500">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Global Status</p>
                    <p class="text-sm text-gray-600 mt-2">All adjustments listed are <strong>PENDING</strong> and will be applied during the next payroll calculation.</p>
                </div>
            </div>

            <!-- Filters & Navigation -->
            <div class="bg-white shadow-sm border border-gray-100 rounded-lg mb-6 overflow-hidden">
                <div class="p-4 bg-gray-50 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex gap-4">
                        <button @click="activeTab = 'bonuses'" :class="activeTab === 'bonuses' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-200'" class="px-4 py-2 rounded-md transition text-sm font-bold">
                            Bonuses ({{ $pendingBonuses->count() }})
                        </button>
                        <button @click="activeTab = 'deductions'" :class="activeTab === 'deductions' ? 'bg-red-600 text-white' : 'bg-white text-gray-600 border border-gray-200'" class="px-4 py-2 rounded-md transition text-sm font-bold">
                            Deductions ({{ $pendingDeductions->count() }})
                        </button>
                        <button @click="activeTab = 'upload'" :class="activeTab === 'upload' ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 border border-gray-200'" class="px-4 py-2 rounded-md transition text-sm font-bold">
                            CSV Upload
                        </button>
                    </div>

                    <form method="GET" class="flex items-center gap-2">
                        <select name="campaign_id" class="border-gray-300 rounded-md shadow-sm text-xs font-bold text-blue-600">
                            <option value="">All Campaigns</option>
                            @foreach($allCampaigns as $campaign)
                                <option value="{{ $campaign->id }}" {{ request('campaign_id') == $campaign->id ? 'selected' : '' }}>{{ $campaign->name }}</option>
                            @endforeach
                        </select>
                        <select name="designation_id" class="border-gray-300 rounded-md shadow-sm text-xs font-bold text-green-600">
                            <option value="">All Job Roles</option>
                            @foreach($allDesignations as $designation)
                                <option value="{{ $designation->id }}" {{ request('designation_id') == $designation->id ? 'selected' : '' }}>{{ $designation->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded-md text-xs font-bold border border-gray-200 hover:bg-gray-200">Apply</button>
                    </form>
                </div>

                <!-- Tab: Bonuses -->
                <div x-show="activeTab === 'bonuses'" class="p-0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Employee</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($pendingBonuses as $bonus)
                                    <tr class="hover:bg-blue-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-900">{{ $bonus->user->name }}</div>
                                            <div class="text-[10px] text-blue-600 font-bold uppercase">{{ $bonus->user->campaign?->name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-bold rounded">{{ $bonus->bonus_code }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-xs text-gray-600 truncate max-w-xs">{{ $bonus->description }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-green-600">₱{{ number_format($bonus->amount, 2) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-xs text-gray-500">{{ $bonus->effective_date->format('M d, Y') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <form action="{{ route('accounting.adjustments.destroy', ['id' => $bonus->id, 'type' => 'bonus']) }}" method="POST" onsubmit="return confirm('Remove this adjustment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic text-sm">No pending bonuses found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab: Deductions -->
                <div x-show="activeTab === 'deductions'" class="p-0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Employee</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($pendingDeductions as $deduction)
                                    <tr class="hover:bg-red-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-900">{{ $deduction->user->name }}</div>
                                            <div class="text-[10px] text-blue-600 font-bold uppercase">{{ $deduction->user->campaign?->name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded">{{ $deduction->deduction_code }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-xs text-gray-600 truncate max-w-xs">{{ $deduction->description }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-red-600">₱{{ number_format($deduction->amount, 2) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-xs text-gray-500">{{ $deduction->effective_date->format('M d, Y') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <form action="{{ route('accounting.adjustments.destroy', ['id' => $deduction->id, 'type' => 'deduction']) }}" method="POST" onsubmit="return confirm('Remove this adjustment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic text-sm">No pending deductions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab: Upload -->
                <div x-show="activeTab === 'upload'" class="p-8">
                    <div class="max-w-2xl mx-auto border-2 border-dashed border-gray-200 rounded-xl p-8 bg-gray-50 text-center">
                        <form action="{{ route('accounting.adjustments.upload') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            <h3 class="text-sm font-bold text-gray-700 mb-2">Bulk Upload Adjustments via CSV</h3>
                            
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div class="text-left">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Type</label>
                                    <select name="adjustment_type" class="w-full border-gray-300 rounded-md text-sm">
                                        <option value="bonus">Bonus / Addition</option>
                                        <option value="deduction">Deduction / Penalty</option>
                                    </select>
                                </div>
                                <div class="text-left">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Effective Date</label>
                                    <input type="date" name="effective_date" required class="w-full border-gray-300 rounded-md text-sm">
                                </div>
                            </div>

                            <input type="file" name="file" required accept=".csv" class="block w-full text-xs text-gray-500 mb-6 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            
                            <button type="submit" class="bg-indigo-600 text-white px-8 py-2 rounded-md font-bold hover:bg-indigo-700 transition w-full">Process Upload</button>
                            
                            <div class="mt-6 p-4 bg-white rounded border border-gray-200 text-left">
                                <p class="text-xs text-gray-500 mb-2 font-bold uppercase tracking-tight">CSV Template Structure:</p>
                                <p class="text-[10px] text-gray-400">employee_id, code, amount, description</p>
                                <p class="text-[10px] text-gray-400 mt-1 italic">Example: MEBS001, ND01, 500.00, Night Shift Differential</p>
                                
                                <div class="mt-4 grid grid-cols-2 gap-4 pt-4 border-t border-gray-50">
                                    <div>
                                        <p class="text-[9px] font-bold text-blue-600 mb-1">COMMON BONUSES</p>
                                        <div class="text-[9px] text-gray-500">
                                            @foreach(collect($commonBonusCodes)->take(5) as $bc)
                                                {{ $bc['code'] }}: {{ $bc['name'] }}<br>
                                            @endforeach
                                            ...
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-bold text-red-600 mb-1">COMMON DEDUCTIONS</p>
                                        <div class="text-[9px] text-gray-500">
                                            @foreach(collect($commonDeductionCodes)->take(5) as $dc)
                                                {{ $dc['code'] }}: {{ $dc['name'] }}<br>
                                            @endforeach
                                            ...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Adjustment Modal -->
    <x-modal name="manual-adjustment-modal" focusable>
        <form method="post" action="{{ route('accounting.adjustments.store') }}" class="p-6">
            @csrf
            <h2 class="text-lg font-bold text-gray-900 mb-4">Add Individual Adjustment</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Employee -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Employee *</label>
                    <div x-data="{ 
                        search: '', 
                        open: false, 
                        selectedId: '', 
                        selectedName: 'Select Employee...',
                        users: [
                            @foreach($allUsers as $u)
                                { id: '{{ $u->id }}', name: '{{ addslashes($u->name) }}', emp_id: '{{ $u->employee_id }}' },
                            @endforeach
                        ],
                        get filteredUsers() {
                            if (this.search === '') return this.users.slice(0, 50);
                            return this.users.filter(u => 
                                u.name.toLowerCase().includes(this.search.toLowerCase()) || 
                                u.emp_id.toLowerCase().includes(this.search.toLowerCase())
                            ).slice(0, 50);
                        },
                        selectUser(user) {
                            this.selectedId = user.id;
                            this.selectedName = user.name + ' (' + user.emp_id + ')';
                            this.open = false;
                        }
                    }" class="relative">
                        <input type="hidden" name="user_id" :value="selectedId" required>
                        <button type="button" @click="open = !open" 
                                class="w-full bg-white border border-gray-300 rounded-md shadow-sm px-4 py-2 text-left text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <span x-text="selectedName"></span>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                                    <path d="M7 7l3-3 3 3m0 6l-3 3-3-3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </button>

                        <div x-show="open" @click.away="open = false" 
                             class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                            <div class="sticky top-0 z-10 bg-white px-2 py-1">
                                <input type="text" x-model="search" placeholder="Search name or ID..." 
                                       class="block w-full px-3 py-1.5 text-sm border-gray-200 rounded-md focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <ul class="mt-1">
                                <template x-for="user in filteredUsers" :key="user.id">
                                    <li @click="selectUser(user)" 
                                        class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white transition-colors cursor-pointer">
                                        <span x-text="user.name" class="font-normal block truncate"></span>
                                        <span x-text="user.emp_id" class="text-[10px] text-gray-500 group-hover:text-indigo-200 block"></span>
                                    </li>
                                </template>
                                <li x-show="filteredUsers.length === 0" class="text-gray-500 py-4 text-center text-xs italic">
                                    No results found
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Type -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Type *</label>
                    <select name="type" required class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="bonus">Bonus / Addition</option>
                        <option value="deduction">Deduction / Penalty</option>
                    </select>
                </div>

                <!-- Code -->
                <div x-data="{ 
                    setCode(code) {
                        const input = $el.closest('form').querySelector('input[name=\'code\']');
                        input.value = code;
                        input.dispatchEvent(new Event('input'));
                    }
                }">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Code * (e.g. ND01)</label>
                    <input type="text" name="code" required placeholder="Type Code (e.g. ND01)" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    <p class="text-[10px] text-gray-500 mt-1">Refer to the list below for standard codes.</p>
                </div>

                <!-- Amount -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Amount (₱) *</label>
                    <input type="number" step="0.01" name="amount" required class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>

                <!-- Effective Date -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Effective Date *</label>
                    <input type="date" name="effective_date" required value="{{ date('Y-m-d') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>

                <!-- Payroll Period (Optional but helpful) -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Assign to Period (Optional)</label>
                    <select name="payroll_period_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Auto-assign based on date...</option>
                        @foreach($periods as $period)
                            <option value="{{ $period->id }}">{{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Internal Description</label>
                    <textarea name="description" rows="2" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Reason for this adjustment..."></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="$dispatch('close')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-bold hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-bold hover:bg-indigo-700 transition shadow-lg">Save Adjustment</button>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Available Code Reference (Click to auto-fill)</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] font-bold text-blue-600 mb-2 underline">BONUS CODES</p>
                        @foreach($commonBonusCodes as $bc)
                            <div @click="document.getElementsByName('code')[0].value = '{{ $bc['code'] }}'" 
                                 class="flex justify-between items-center mb-1 text-[10px] cursor-pointer hover:bg-blue-50 p-0.5 rounded transition">
                                <span class="font-mono bg-blue-50 text-blue-700 px-1 rounded">{{ $bc['code'] }}</span>
                                <span class="text-gray-500">{{ $bc['name'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-red-600 mb-2 underline">DEDUCTION CODES</p>
                        @foreach($commonDeductionCodes as $dc)
                            <div @click="document.getElementsByName('code')[0].value = '{{ $dc['code'] }}'" 
                                 class="flex justify-between items-center mb-1 text-[10px] cursor-pointer hover:bg-red-50 p-0.5 rounded transition">
                                <span class="font-mono bg-red-50 text-red-700 px-1 rounded">{{ $dc['code'] }}</span>
                                <span class="text-gray-500">{{ $dc['name'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </form>
    </x-modal>
</x-app-layout>
