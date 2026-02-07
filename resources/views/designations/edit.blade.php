<x-app-layout>
    <div class="p-6 bg-[#f8fafc] min-h-screen">
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center gap-2 mb-6 text-gray-400">
                <a href="{{ route('designations.index') }}" class="hover:text-gray-600 transition">Designations</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg>
                <span class="text-gray-700 font-bold">Edit Designation</span>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-yellow-500 px-6 py-4">
                    <h2 class="text-white font-bold uppercase tracking-widest text-sm">Update Designation</h2>
                </div>

                <div class="p-8">
                    <form method="POST" action="{{ route('designations.update', $designation) }}" class="space-y-6">
                        @csrf @method('PUT')
                        <div>
                            <label for="name" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Designation Name *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $designation->name) }}" required
                                class="w-full border-gray-200 rounded-lg focus:border-yellow-500 focus:ring-yellow-500 text-gray-700 font-medium">
                            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="description" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Role Description</label>
                            <textarea name="description" id="description" rows="4"
                                class="w-full border-gray-200 rounded-lg focus:border-yellow-500 focus:ring-yellow-500 text-gray-700">{{ old('description', $designation->description) }}</textarea>
                            @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                            <a href="{{ route('designations.index') }}" class="px-6 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-700 transition">Cancel</a>
                            <button type="submit" class="px-8 py-2.5 bg-yellow-500 text-white rounded-lg font-bold hover:bg-yellow-600 transition shadow-lg">
                                Update Designation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
