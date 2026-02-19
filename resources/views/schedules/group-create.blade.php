<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center bg-red-800 text-white p-4 -m-4 sm:-m-6 lg:-m-8 mb-4">
            <h2 class="font-bold text-lg uppercase tracking-wider">
                MAASIN ADMINS - GROUP PLOTTING
            </h2>
            <a href="{{ route('schedules.index') }}" class="hover:opacity-80">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.707-10.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L9.414 11H13a1 1 0 100-2H9.414l1.293-1.293z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-blue-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100">
                <div class="p-8">
                    <form method="POST" action="{{ route('schedules.group-store') }}">
                        @csrf
                        
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                            {{-- Step 1: Select Employees --}}
                            <div class="space-y-4">
                                <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest border-b pb-2">1. Select Employees</h3>
                                
                                <div class="bg-slate-50 p-4 rounded-md space-y-3">
                                    <div class="text-xs font-bold text-slate-500 uppercase">Filters:</div>
                                    <div class="space-y-2">
                                        <select class="w-full text-xs rounded border-gray-200" onchange="window.location.href='?account_id='+this.value">
                                            <option value="">Filter by Account...</option>
                                            @foreach($accounts as $acc)
                                                <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="max-h-[500px] overflow-y-auto border border-gray-100 rounded bg-gray-50/30">
                                    <table class="min-w-full divide-y divide-gray-100">
                                        <thead class="bg-gray-100 sticky top-0">
                                            <tr>
                                                <th class="px-3 py-2 text-left">
                                                    <input type="checkbox" id="select-all" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                                </th>
                                                <th class="px-3 py-2 text-left text-[10px] font-black uppercase text-gray-500">Employee</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-50 bg-white">
                                            @forelse($users as $user)
                                                <tr class="hover:bg-red-50/30">
                                                    <td class="px-3 py-2">
                                                        <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="employee-cb rounded border-gray-300 text-red-600 focus:ring-red-500">
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <div class="text-[11px] font-bold text-gray-800 uppercase">{{ $user->name }}</div>
                                                        <div class="text-[10px] text-gray-500">{{ $user->employee_id }}</div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="2" class="p-4 text-center text-xs text-gray-400 italic">No employees found.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Step 2: Set Weekly Schedule --}}
                            <div class="lg:col-span-2 space-y-6">
                                <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest border-b pb-2">2. Set Weekly Schedule</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                                    @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                        <div>
                                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1">{{ $day }}</label>
                                            <select name="{{ $day }}_schedule" class="w-full border-gray-200 rounded text-sm focus:ring-red-500 focus:border-red-500">
                                                <option value="Rest day">Rest day</option>
                                                @foreach($schedules as $shift)
                                                    @php
                                                        $shiftTime = \Carbon\Carbon::parse($shift->work_start_time)->format('H:i') . ' to ' . \Carbon\Carbon::parse($shift->work_end_time)->format('H:i');
                                                    @endphp
                                                    <option value="{{ $shiftTime }}">{{ $shiftTime }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endforeach

                                    {{-- Special Options Section --}}
                                    <div class="md:col-start-2 pt-4 border-t border-slate-100 lg:border-t-0 lg:pt-0">
                                        <div class="space-y-6">
                                            <div>
                                                <div class="flex items-center mb-2">
                                                    <label class="font-black text-[11px] text-gray-800 uppercase tracking-widest mr-2">Special 1 Hour Only</label>
                                                    <input type="hidden" name="special_1_hour_only" value="0">
                                                    <input type="checkbox" name="special_1_hour_only" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                                </div>
                                            </div>

                                            <div>
                                                <div class="flex items-center mb-2">
                                                    <label class="font-black text-[11px] text-gray-800 uppercase tracking-widest mr-2">Special Case / Present Policy</label>
                                                    <input type="hidden" name="special_case_policy" value="0">
                                                    <input type="checkbox" name="special_case_policy" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-12">
                                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-4 rounded shadow-lg transition-all transform hover:-translate-y-0.5 active:translate-y-0 uppercase tracking-widest text-xs flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                        </svg>
                                        APPLY SCHEDULE TO ALL SELECTED EMPLOYEES
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('select-all').addEventListener('change', function() {
            document.querySelectorAll('.employee-cb').forEach(cb => {
                cb.checked = this.checked;
            });
        });
    </script>
    @endpush
</x-app-layout>