<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-slate-900 leading-tight tracking-tight uppercase">
                    Bulletin <span class="text-blue-600">Feed</span>
                </h2>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">
                    {{ request()->has('manage') ? 'System Broadcast Management' : 'Company-wide Broadcasts & Directives' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                @if(request()->has('manage') && (auth()->user()->isSuperAdmin() || auth()->user()->isHr()))
                <a href="{{ route('announcements.create') }}" 
                   class="px-6 py-3 bg-slate-900 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-200">
                    Propagate Alert
                </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            @forelse($announcements ?? [] as $announcement)
                <div class="bg-white/40 backdrop-blur-xl border border-white/60 p-10 rounded-[3rem] shadow-sm relative group overflow-hidden hover:bg-white/50 transition-all">
                    
                    @if($announcement->is_pinned)
                    <div class="absolute top-0 right-0">
                        <div class="bg-blue-600 text-white text-[8px] font-black uppercase tracking-widest px-6 py-2 rounded-bl-[1.5rem] shadow-lg shadow-blue-100 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
                            Priority Directive
                        </div>
                    </div>
                    @endif

                    <div class="flex justify-between items-start gap-8">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-4 mb-4">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest bg-slate-100/50 px-3 py-1.5 rounded-xl border border-slate-200/50">
                                    {{ $announcement->created_at->format('M d, Y') }}
                                </span>
                                <div class="flex items-center gap-2 text-[9px] font-black text-blue-500 uppercase tracking-widest">
                                    <div class="w-5 h-5 bg-slate-900 rounded-lg flex items-center justify-center text-white text-[8px]">
                                        {{ substr($announcement->author->name, 0, 1) }}
                                    </div>
                                    {{ $announcement->author->name }}
                                </div>
                            </div>

                            <h3 class="text-2xl font-black text-slate-900 tracking-tight leading-tight uppercase mb-6 group-hover:translate-x-1 transition-transform">
                                {{ $announcement->title }}
                            </h3>

                            <div class="prose max-w-none text-slate-600 text-sm font-medium leading-relaxed tracking-tight">
                                {!! nl2br(e($announcement->content)) !!}
                            </div>
                        </div>

                        <!-- Management Actions -->
                        @if(request()->has('manage') && (auth()->user()->role === 'super_admin' || auth()->user()->role === 'hr'))
                        <div class="flex flex-col gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                            <form method="POST" action="{{ route('announcements.pin', $announcement) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="p-3 bg-white/80 backdrop-blur-sm border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-200 rounded-2xl shadow-sm transition-all" title="{{ $announcement->is_pinned ? 'Unpin' : 'Pin' }}">
                                    <svg class="w-4 h-4" fill="{{ $announcement->is_pinned ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" /></svg>
                                </button>
                            </form>
                            
                            <a href="{{ route('announcements.edit', $announcement) }}" class="p-3 bg-white/80 backdrop-blur-sm border border-slate-200 text-slate-400 hover:text-emerald-600 hover:border-emerald-200 rounded-2xl shadow-sm transition-all" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </a>

                            <form method="POST" action="{{ route('announcements.destroy', $announcement) }}" onsubmit="return confirm('Permanent deletion? Data recovery is unavailable.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-3 bg-white/80 backdrop-blur-sm border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 rounded-2xl shadow-sm transition-all" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-32 text-center bg-white/40 backdrop-blur-xl border border-white/60 rounded-[3rem] shadow-sm flex flex-col items-center gap-4">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center text-slate-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Silence in the Nexus</p>
                        <p class="text-xs font-bold text-slate-300 uppercase tracking-widest">No active system broadcasts found</p>
                    </div>
                    @if(request()->has('manage') && (auth()->user()->isSuperAdmin() || auth()->user()->isHr()))
                    <a href="{{ route('announcements.create') }}" class="mt-6 px-8 py-3 bg-slate-900 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl shadow-xl shadow-slate-200 hover:scale-105 transition-transform">
                        Initiate First Alert
                    </a>
                    @endif
                </div>
            @endforelse
            
            <div class="mt-8">
                @if(isset($announcements) && method_exists($announcements, 'links'))
                    {{ $announcements->links() }}
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
