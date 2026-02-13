<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('HR Policies') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">Company Policies & Documents</h3>
                        @if(auth()->user()->hasRole('super_admin'))
                        <a href="{{ route('hr-policies.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add New Policy
                        </a>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @forelse($policies as $policy)
                        <div class="border rounded-xl p-6 hover:shadow-md transition-shadow bg-white relative group">
                            @if(!$policy->is_published)
                                <div class="absolute top-4 right-4 bg-yellow-100 text-yellow-800 text-xs font-bold px-2 py-1 rounded-full border border-yellow-200">
                                    DRAFT
                                </div>
                            @endif
                            
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-900 line-clamp-1">{{ $policy->title }}</h4>
                                        <p class="text-xs text-gray-500">Effective: {{ $policy->effective_date?->format('F d, Y') ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="prose max-w-none text-gray-600 mb-6 text-sm line-clamp-3 h-16">
                                {{ Str::limit($policy->content, 150) }}
                            </div>

                            <div class="flex items-center justify-between mt-auto border-t pt-4">
                                <a href="{{ route('hr-policies.show', $policy) }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm flex items-center gap-1 group-hover:underline">
                                    Read Full Policy 
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                                
                                <div class="flex items-center gap-3">
                                    @if($policy->attachment_path)
                                    <a href="{{ Storage::url($policy->attachment_path) }}" target="_blank" class="text-gray-400 hover:text-gray-600 tooltip" title="Download PDF">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </a>
                                    @endif

                                    @if(auth()->user()->hasRole('super_admin'))
                                        <div class="flex items-center gap-2 border-l pl-3 ml-2">
                                            <a href="{{ route('hr-policies.edit', $policy) }}" class="text-blue-500 hover:text-blue-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            <form action="{{ route('hr-policies.destroy', $policy) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this policy?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-1 md:col-span-2 text-center py-12 text-gray-500 bg-gray-50 rounded-lg dashed-border border-2 border-dashed border-gray-300">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-lg font-medium">No policies found.</p>
                            @if(auth()->user()->hasRole('super_admin'))
                                <p class="text-sm mt-2">Get started by adding a new policy.</p>
                            @else
                                <p class="text-sm mt-2">Check back later for company updates.</p>
                            @endif
                        </div>
                        @endforelse
                    </div>

                    <div class="mt-6">
                        {{ $policies->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
