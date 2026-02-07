<x-app-layout>
    <div class="p-6 bg-[#f8fafc] min-h-screen font-sans">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Campaign: <span class="text-blue-600">{{ $campaign->name }}</span>
                </h1>
                <a href="{{ route('campaigns.index') }}" class="text-sm font-bold text-gray-500 hover:text-gray-700 flex items-center gap-1 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to List
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-8">
                    <form method="POST" action="{{ route('campaigns.update', $campaign) }}">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Campaign Name -->
                            <div class="space-y-2">
                                <label for="name" class="block text-sm font-bold text-gray-700 uppercase tracking-tight">Campaign / Account Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name', $campaign->name) }}" required
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition shadow-sm p-3 font-medium text-gray-900">
                                @error('name') <p class="text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <!-- Site -->
                            <div class="space-y-2">
                                <label for="site_id" class="block text-sm font-bold text-gray-700 uppercase tracking-tight">Site Location <span class="text-red-500">*</span></label>
                                <select name="site_id" id="site_id" required
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition shadow-sm p-3 uppercase font-bold text-xs text-gray-700">
                                    <option value="">Select a Site</option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site->id }}" {{ old('site_id', $campaign->site_id) == $site->id ? 'selected' : '' }}>
                                            {{ $site->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('site_id') <p class="text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2 space-y-2">
                                <label for="description" class="block text-sm font-bold text-gray-700 uppercase tracking-tight">Campaign Description</label>
                                <textarea name="description" id="description" rows="4"
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition shadow-sm p-3 text-gray-700">{{ old('description', $campaign->description) }}</textarea>
                                @error('description') <p class="text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-10 pt-6 border-t border-gray-100 flex justify-between items-center">
                            <div class="text-xs text-gray-400 font-medium italic">
                                Created on {{ $campaign->created_at->format('M d, Y') }}
                            </div>
                            <div class="flex gap-3">
                                <a href="{{ route('campaigns.index') }}" class="bg-gray-100 text-gray-600 px-6 py-3 rounded-lg hover:bg-gray-200 transition font-bold text-sm uppercase tracking-wider">
                                    Cancel Changes
                                </a>
                                <button type="submit" class="bg-blue-600 text-white px-10 py-3 rounded-lg hover:bg-blue-700 transition font-bold text-sm uppercase tracking-wider shadow-lg shadow-blue-200">
                                    Update Campaign
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            @php
                $empCount = \App\Models\User::where('campaign_id', $campaign->id)->count();
            @endphp
            @if($empCount > 0)
                <div class="mt-8 bg-blue-50 border border-blue-100 rounded-lg p-4 flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <h4 class="text-sm font-bold text-blue-800">Operational Note</h4>
                        <p class="text-xs text-blue-600 font-medium">There are currently <span class="underline font-bold">{{ $empCount }} employees</span> assigned to this campaign. Changing the site location will affect their processing.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
