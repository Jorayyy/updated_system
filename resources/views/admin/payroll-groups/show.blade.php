<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('payroll-groups.index') }}" class="text-gray-500 hover:text-gray-700" title="Back to Groups">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Payroll Group Details: ') . $payrollGroup->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Group Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Group Information</h3>
                            <div class="mt-2 text-sm text-gray-600">
                                <p><span class="font-semibold">Period Type:</span> {{ ucfirst($payrollGroup->period_type) }}</p>
                                <p><span class="font-semibold">Status:</span> {{ $payrollGroup->is_active ? 'Active' : 'Inactive' }}</p>
                                <p><span class="font-semibold">Total Employees:</span> {{ $payrollGroup->users->count() }}</p>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('payroll-groups.edit', $payrollGroup) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Edit Details</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Employees Management -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Assigned Employees</h3>
                        
                        <!-- Add Employee Form -->
                        <form action="{{ route('payroll-groups.add-employee', $payrollGroup) }}" method="POST" class="mb-6 flex gap-2">
                            @csrf
                            <div class="flex-grow">
                                <select name="user_id" class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    <option value="">-- Select Employee to Add --</option>
                                    @foreach($availableUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Add</button>
                        </form>

                        <div class="flex flex-wrap gap-2 mb-6">
                            <a href="{{ route('schedules.group-create', ['payroll_group_id' => $payrollGroup->id]) }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 transition ease-in-out duration-150">
                                📅 Plot Group Schedule
                            </a>
                        </div>

                        <!-- Employee List -->
                        <div class="overflow-y-auto max-h-96">
                            <ul class="divide-y divide-gray-200">
                                @forelse($payrollGroup->users as $user)
                                    <li class="py-3 flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ $user->full_name }}</p>
                                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                        <form action="{{ route('payroll-groups.remove-employee', ['payrollGroup' => $payrollGroup, 'user' => $user]) }}" method="POST" onsubmit="return confirm('Remove this employee from the group?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 text-sm">Remove</button>
                                        </form>
                                    </li>
                                @empty
                                    <li class="py-4 text-center text-gray-500 text-sm">No employees assigned to this group.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Recent Payroll Periods -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Recent Payroll Periods</h3>
                            <a href="{{ route('payroll.create-period', ['payroll_group_id' => $payrollGroup->id]) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                ➕ Generate Period
                            </a>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($payrollGroup->periods as $period)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }}
                                            </td>
                                            <td class="px-4 py-2 text-sm">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $period->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                                       ($period->status === 'processing' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                                    {{ ucfirst($period->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-500">
                                                <a href="{{ route('payroll.show-period', $period) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-4 text-center text-gray-500 text-sm">No payroll periods generated for this group yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
