<x-app-layout>
    <div class="p-6 bg-[#f8fafc] min-h-screen font-sans">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Create New Campaign
            </h1>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-8">
                    <form method="POST" action="{{ route('campaigns.store') }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Campaign Name -->
                            <div class="space-y-2">
                                <label for="name" class="block text-sm font-bold text-gray-700 uppercase tracking-tight">Campaign / Account Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                    placeholder="e.g. eBay Customer Support"
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition shadow-sm p-3">
                                @error('name') <p class="text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <!-- Site -->
                            <div class="space-y-2">
                                <label for="site_id" class="block text-sm font-bold text-gray-700 uppercase tracking-tight">Site Location <span class="text-red-500">*</span></label>
                                <select name="site_id" id="site_id" required
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition shadow-sm p-3">
                                    <option value="">Select a Site</option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site->id }}" {{ old('site_id') == $site->id ? 'selected' : '' }}>
                                            {{ $site->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('site_id') <p class="text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2 space-y-2">
                                <label for="description" class="block text-sm font-bold text-gray-700 uppercase tracking-tight">Description</label>
                                <textarea name="description" id="description" rows="4"
                                    placeholder="Provide a brief overview of what this campaign handles..."
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition shadow-sm p-3">{{ old('description') }}</textarea>
                                @error('description') <p class="text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-10 pt-6 border-t border-gray-100 flex justify-end gap-3">
                            <a href="{{ route('campaigns.index') }}" class="bg-gray-100 text-gray-600 px-6 py-3 rounded-lg hover:bg-gray-200 transition font-bold text-sm uppercase tracking-wider">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-600 text-white px-10 py-3 rounded-lg hover:bg-blue-700 transition font-bold text-sm uppercase tracking-wider shadow-lg shadow-blue-200">
                                Save Campaign
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
