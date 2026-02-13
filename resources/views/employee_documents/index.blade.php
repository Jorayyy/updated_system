<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My 201 File (Documents)') }}
            </h2>
            <a href="{{ route('employee-documents.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors duration-200">
                Upload Document
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @forelse($documents ?? [] as $document)
                            <div class="border rounded-lg p-4 hover:shadow-lg transition-shadow bg-gray-50">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="text-indigo-600">
                                        <!-- Icon based on type lookup or generic -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    @if($document->expiry_date)
                                      <div class="text-xs {{ \Carbon\Carbon::parse($document->expiry_date)->isPast() ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                                          Expires: {{ $document->expiry_date->format('M d, Y') }}
                                      </div>
                                    @endif
                                </div>
                                <h3 class="font-bold text-lg mb-1">{{ $document->type }}</h3>
                                <p class="text-sm text-gray-600 mb-4">{{ $document->description ?? $document->file_name }}</p>
                                
                                <a href="{{ route('employee-documents.download', $document) }}" target="_blank" class="block w-full text-center bg-white border border-gray-300 text-gray-700 py-2 rounded-md hover:bg-gray-50">
                                    Download / View
                                </a>
                            </div>
                        @empty
                            <div class="col-span-3 text-center py-8 text-gray-500">
                                No documents uploaded yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
