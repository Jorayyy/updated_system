<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Shift Request Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-xl font-bold mb-2">Request Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-600">Requested Date:</p>
                                <p class="font-semibold">{{ $shiftChangeRequest->requested_date->format('F d, Y') }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Status:</p>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $shiftChangeRequest->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                      ($shiftChangeRequest->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ ucfirst($shiftChangeRequest->status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded">
                                <p class="text-gray-500 text-sm uppercase">Current Schedule</p>
                                <p class="font-medium text-lg">{{ $shiftChangeRequest->current_schedule }}</p>
                            </div>
                             <div class="bg-blue-50 p-4 rounded border border-blue-100">
                                <p class="text-blue-500 text-sm uppercase">New Proposed Schedule</p>
                                <p class="font-bold text-lg text-blue-800">{{ $shiftChangeRequest->new_schedule }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-700">Reason</h4>
                        <p class="mt-2 text-gray-600 bg-gray-50 p-3 rounded">{{ $shiftChangeRequest->reason }}</p>
                    </div>

                    @if($shiftChangeRequest->admin_remarks)
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-700">Admin Remarks</h4>
                        <p class="mt-2 text-gray-600 bg-gray-50 p-3 rounded">{{ $shiftChangeRequest->admin_remarks }}</p>
                    </div>
                    @endif

                    <div class="mt-8 flex justify-end">
                        <a href="{{ route('shift-change-requests.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                            Back to Requests
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
