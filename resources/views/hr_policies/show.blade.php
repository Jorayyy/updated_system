<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Policy Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6 border-b pb-4">
                        <h3 class="text-3xl font-bold text-gray-800">{{ $hrPolicy->title }}</h3>
                        <p class="text-sm text-gray-500 mt-2">
                            Effective Date: {{ $hrPolicy->effective_date->format('F d, Y') }} | 
                            Category: {{ $hrPolicy->category }}
                        </p>
                    </div>

                    <div class="prose max-w-none text-gray-700 mb-8">
                        {!! nl2br(e($hrPolicy->content)) !!}
                    </div>

                    @if($hrPolicy->attachment_path)
                    <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-6 flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-8 h-8 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <div>
                                <p class="font-semibold text-gray-800">Attached Document</p>
                                <p class="text-sm text-gray-500">Click to download/view the official policy document.</p>
                            </div>
                        </div>
                        <a href="{{ asset('storage/' . $hrPolicy->attachment_path) }}" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                            Download PDF
                        </a>
                    </div>
                    @endif

                    <div class="flex justify-end">
                        <a href="{{ route('hr-policies.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                            Back to Policies
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
