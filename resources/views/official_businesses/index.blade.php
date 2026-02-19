<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-slate-900 leading-tight tracking-tight uppercase">
                    External <span class="text-blue-600">Operations</span>
                </h2>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Official Business & Client Engagement Logs</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('official-businesses.create') }}" 
                   class="px-6 py-3 bg-slate-900 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-200">
                    Register Departure
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
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Temporal Window</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Target / Vector</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Operational Objective</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Verification</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Authorized By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($businesses ?? [] as $business)
                            <tr class="group">
                                <td class="px-6 py-5 bg-white/40 backdrop-blur-xl border-y border-l border-white/60 rounded-l-[2rem] transition-all group-hover:bg-white/60">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-white rounded-2xl flex flex-col items-center justify-center border border-slate-100 shadow-sm">
                                            <span class="text-[8px] font-black text-slate-400 uppercase">{{ $business->date->format('M') }}</span>
                                            <span class="text-sm font-black text-slate-900">{{ $business->date->format('d') }}</span>
                                        </div>
                                        <div>
                                            <div class="font-black text-slate-900 uppercase tracking-tight text-[11px]">{{ $business->date->format('l') }}</div>
                                            <div class="text-[9px] font-bold text-blue-600 uppercase tracking-widest mt-0.5">
                                                {{ \Carbon\Carbon::parse($business->start_time)->format('h:i A') }} — {{ \Carbon\Carbon::parse($business->end_time)->format('h:i A') }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 bg-white/40 backdrop-blur-xl border-y border-white/60 transition-all group-hover:bg-white/60">
                                    <div class="font-black text-slate-900 uppercase tracking-tight text-xs">{{ $business->client_name }}</div>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest truncate max-w-[150px]">{{ $business->location }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 bg-white/40 backdrop-blur-xl border-y border-white/60 transition-all group-hover:bg-white/60">
                                    <p class="text-xs font-medium text-slate-600 leading-relaxed max-w-xs line-clamp-2 italic">"{{ $business->purpose }}"</p>
                                </td>
                                <td class="px-6 py-5 bg-white/40 backdrop-blur-xl border-y border-white/60 transition-all group-hover:bg-white/60">
                                    @php
                                        $statusClasses = [
                                            'approved' => 'bg-emerald-100/50 text-emerald-600 border-emerald-200/50',
                                            'rejected' => 'bg-rose-100/50 text-rose-600 border-rose-200/50',
                                            'pending' => 'bg-amber-100/50 text-amber-600 border-amber-200/50',
                                        ];
                                        $statusLabels = [
                                            'approved' => 'Authorized',
                                            'rejected' => 'Declined',
                                            'pending' => 'Evaluating',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1.5 inline-flex text-[9px] font-black uppercase tracking-widest rounded-xl border {{ $statusClasses[$business->status] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $statusLabels[$business->status] ?? $business->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 bg-white/40 backdrop-blur-xl border-y border-r border-white/60 rounded-r-[2rem] text-right transition-all group-hover:bg-white/60">
                                    <div class="flex items-center justify-end gap-3">
                                        <div class="text-right">
                                            <div class="font-black text-slate-900 uppercase tracking-tight text-[10px]">{{ $business->approver->name ?? 'System' }}</div>
                                            <div class="text-[8px] font-bold text-slate-400 uppercase tracking-widest leading-none mt-0.5">{{ $business->approver ? 'Authorized Officer' : 'Awaiting Audit' }}</div>
                                        </div>
                                        <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-[10px] font-black text-slate-400">
                                            {{ substr($business->approver->name ?? 'S', 0, 1) }}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-24 text-center bg-white/40 backdrop-blur-xl border border-white/60 rounded-[3rem]">
                                    <div class="max-w-xs mx-auto flex flex-col items-center gap-4">
                                        <div class="w-16 h-16 bg-slate-100 rounded-[2rem] flex items-center justify-center text-slate-300">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Operational Stasis</p>
                                            <p class="text-xs font-bold text-slate-300 uppercase tracking-widest">No external business logs recorded in the current cycle</p>
                                        </div>
                                        <a href="{{ route('official-businesses.create') }}" class="mt-4 px-8 py-3 bg-slate-900 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl shadow-xl shadow-slate-200">
                                            Initiate Pass
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(isset($businesses) && method_exists($businesses, 'links'))
            <div class="mt-8">
                {{ $businesses->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
