<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-slate-900 leading-tight tracking-tight uppercase">
                    Structural <span class="text-blue-600">Sectors</span>
                </h2>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Organizational Hierarchy & Departmental Mapping</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('departments.create') }}" 
                   class="px-6 py-3 bg-slate-900 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-200">
                    Architect New Sector
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-y-3">
                    <thead>
                        <tr class="text-left">
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Designation</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Operational Scope</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Vitality</th>
                            <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Workforce</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Operations</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $department)
                            <tr class="group">
                                <td class="px-6 py-5 bg-white/40 backdrop-blur-xl border-y border-l border-white/60 rounded-l-[2rem] transition-all group-hover:bg-white/60">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-slate-900 rounded-[1.2rem] flex items-center justify-center text-white font-black text-sm shadow-lg shadow-slate-200">
                                            {{ substr($department->name, 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="font-black text-slate-900 uppercase tracking-tight text-sm">{{ $department->name }}</div>
                                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Sector ID: DEP-{{ str_pad($department->id, 3, '0', STR_PAD_LEFT) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 bg-white/40 backdrop-blur-xl border-y border-white/60 transition-all group-hover:bg-white/60">
                                    <p class="text-xs font-medium text-slate-600 line-clamp-1 max-w-xs lowercase first-letter:uppercase">{{ $department->description ?? 'No operational description provided.' }}</p>
                                </td>
                                <td class="px-6 py-5 bg-white/40 backdrop-blur-xl border-y border-white/60 transition-all group-hover:bg-white/60">
                                    <span class="px-3 py-1.5 inline-flex text-[9px] font-black uppercase tracking-widest rounded-xl transition-all {{ $department->is_active ? 'bg-emerald-100/50 text-emerald-600 border border-emerald-200/50' : 'bg-rose-100/50 text-rose-600 border border-rose-200/50' }}">
                                        {{ $department->is_active ? 'Fully Operational' : 'Offline' }}
                                    </span>
                                </td>
                                <td class="px-4 py-5 bg-white/40 backdrop-blur-xl border-y border-white/60 text-center transition-all group-hover:bg-white/60">
                                    <div class="inline-flex items-center justify-center w-10 h-10 bg-slate-100 rounded-2xl text-slate-900 font-black text-xs border border-slate-200/50">
                                        {{ $department->users_count ?? $department->users()->count() }}
                                    </div>
                                </td>
                                <td class="px-6 py-5 bg-white/40 backdrop-blur-xl border-y border-r border-white/60 rounded-r-[2rem] text-right transition-all group-hover:bg-white/60">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('departments.edit', $department) }}" 
                                           class="p-2.5 bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-200 rounded-xl shadow-sm transition-all group-hover:scale-105"
                                           title="Modify Sector">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <form action="{{ route('departments.destroy', $department) }}" method="POST" class="inline" onsubmit="return confirm('Execute sector decommissioning? All departmental links will be severed.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-2.5 bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 rounded-xl shadow-sm transition-all group-hover:scale-105"
                                                    title="Decommission">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-20 text-center bg-white/40 backdrop-blur-xl border border-white/60 rounded-[3rem]">
                                    <div class="max-w-xs mx-auto flex flex-col items-center gap-4">
                                        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center text-slate-300">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Structural Void</p>
                                            <p class="text-xs font-bold text-slate-300 uppercase tracking-widest leading-relaxed">No operational sectors have been mapped to the mainframe architecture</p>
                                        </div>
                                        <a href="{{ route('departments.create') }}" class="mt-4 px-8 py-3 bg-slate-900 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl shadow-xl shadow-slate-200">
                                            Initialize Core Sector
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(isset($departments) && method_exists($departments, 'links'))
            <div class="mt-8">
                {{ $departments->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
