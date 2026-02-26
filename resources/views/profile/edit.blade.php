<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <h2 class="font-black text-2xl text-slate-800 uppercase tracking-tighter">
                My <span class="text-blue-600">Profile</span>
            </h2>
        </div>
    </x-slot>

    <div class="space-y-10">
        <div class="max-w-7xl mx-auto space-y-10">
            {{-- User Overview Header --}}
            <div class="bg-white/40 backdrop-blur-xl border border-white/60 p-10 rounded-[2.5rem] shadow-sm relative overflow-hidden group">
                <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-10">
                    <div class="flex flex-col md:flex-row items-center gap-8 flex-1">
                        {{-- Name and Basic Contact --}}
                        <div class="text-center md:text-left">
                            <h3 class="text-4xl font-black text-slate-900 uppercase tracking-tighter leading-none mb-3">{{ $user->name }}</h3>
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center justify-center md:justify-start text-slate-400">
                                    <span class="text-[10px] font-black uppercase tracking-[0.15em]">{{ $user->email }}</span>
                                </div>
                                <div class="flex items-center justify-center md:justify-start text-slate-400">
                                    <span class="text-[10px] font-black uppercase tracking-[0.15em]">Hired: {{ $user->date_hired ? $user->date_hired->format('M Y') : 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Badges and Credentials --}}
                    <div class="flex flex-wrap justify-center items-center gap-3 bg-slate-50/50 p-6 rounded-[2rem] border border-slate-100">
                        <div class="flex flex-col items-center px-4 border-r border-slate-200">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Access Level</span>
                            <span class="px-4 py-1.5 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-slate-200">
                                {{ $user->role }}
                            </span>
                        </div>
                        <div class="flex flex-col items-center px-4 border-r border-slate-200">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Asset ID</span>
                            <span class="px-4 py-1.5 bg-white border border-slate-200 text-slate-500 text-[10px] font-black uppercase tracking-widest rounded-xl shadow-sm">
                                {{ $user->employee_id }}
                            </span>
                        </div>
                        <div class="flex flex-col items-center px-4">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Department</span>
                            <span class="px-4 py-1.5 bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest rounded-xl border border-blue-100 italic">
                                {{ $user->department->name ?? 'None' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Profile Info Section --}}
                <div class="bg-white/40 backdrop-blur-xl border border-white/60 p-10 rounded-[2.5rem] shadow-sm hover:bg-white/50 transition-all">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <div class="space-y-8">
                    {{-- Password Section --}}
                    <div class="bg-white/40 backdrop-blur-xl border border-white/60 p-10 rounded-[2.5rem] shadow-sm hover:bg-white/50 transition-all">
                        @include('profile.partials.update-password-form')
                    </div>

                    {{-- Delete Section --}}
                    <div class="bg-rose-50/30 backdrop-blur-sm border border-rose-100 p-10 rounded-[2.5rem] shadow-sm hover:bg-rose-50/50 transition-all group">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
