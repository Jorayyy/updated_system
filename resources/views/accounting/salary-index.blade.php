<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Salary Management') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('accounting.adjustments.index') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors duration-200 text-sm">
                    Bulk Adjustments
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filter by Site, Campaign, Role -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('accounting.salaries.index') }}" class="flex flex-wrap items-end gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Site</label>
                            <select name="site_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Sites</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                                        {{ $site->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-bold text-blue-600 mb-1">Filter by Campaign</label>
                            <select name="campaign_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-bold text-blue-600 uppercase text-xs">
                                <option value="">All Campaigns</option>
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}" {{ request('campaign_id') == $campaign->id ? 'selected' : '' }}>
                                        {{ $campaign->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-bold text-green-600 mb-1">Filter by Job Role</label>
                            <select name="designation_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 font-bold text-green-600 uppercase text-xs">
                                <option value="">All Job Roles</option>
                                @foreach($designations as $designation)
                                    <option value="{{ $designation->id }}" {{ request('designation_id') == $designation->id ? 'selected' : '' }}>
                                        {{ $designation->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-900 transition-colors duration-200">
                            Apply Filter
                        </button>
                        @if(request('site_id') || request('campaign_id') || request('designation_id'))
                            <a href="{{ route('accounting.salaries.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">Clear</a>
                        @endif
                    </form>
                </div>
            </div>

            @forelse($users as $siteName => $siteUsers)
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-gray-700 mb-4 bg-gray-100 p-3 rounded-lg border-l-4 border-indigo-500 flex justify-between items-center">
                        <span>Site: {{ $siteName }}</span>
                        <span class="text-sm font-normal text-gray-500">{{ $siteUsers->count() }} Employees</span>
                    </h3>
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 text-gray-600">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Employee</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Position</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider">Hourly Rate</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider">Daily Rate</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider">Monthly Base</th>
                                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Allowances</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($siteUsers as $user)
                                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="text-sm">
                                                        <div class="font-bold text-gray-900">{{ $user->name }}</div>
                                                        <div class="text-[10px] text-gray-500">ID: {{ $user->employee_id }}</div>
                                                        <div class="text-[10px] font-bold text-blue-600 uppercase">{{ $user->campaign?->name ?? 'No Campaign' }}</div>
                                                        <div class="text-[10px] font-bold text-green-600 uppercase">{{ $user->designation?->name ?? 'No Job Role' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ $user->position }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                                ₱{{ number_format($user->hourly_rate, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                                ₱{{ number_format($user->daily_rate, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-indigo-600">
                                                ₱{{ number_format($user->monthly_salary, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="flex flex-col text-xs text-gray-500">
                                                    @if($user->meal_allowance > 0)
                                                        <span>Meal: ₱{{ number_format($user->meal_allowance, 2) }}</span>
                                                    @endif
                                                    @if($user->transportation_allowance > 0)
                                                        <span>Trans: ₱{{ number_format($user->transportation_allowance, 2) }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('accounting.salaries.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1 rounded-md transition-colors duration-200">
                                                    Manage
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white p-8 rounded-lg shadow text-center">
                    <p class="text-gray-500">No employees found for the selected criteria.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
