<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-slate-800 uppercase tracking-tighter">
            System <span class="text-blue-600">Overview</span>
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white/40 backdrop-blur-xl border border-white/60 p-10 rounded-[2.5rem] shadow-sm">
                <div class="flex items-center gap-6">
                    <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-200">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tighter">{{ __("Welcome Back") }}</h3>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">{{ __("You are currently authenticated to the system.") }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
