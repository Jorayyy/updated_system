<x-app-layout>
    <x-slot name="header">
        <div class="w-full flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('settings.index') }}" class="group bg-white/40 backdrop-blur-xl p-3 rounded-2xl border border-white/60 hover:bg-slate-900 transition-all duration-300">
                    <svg class="w-5 h-5 text-slate-900 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h2 class="font-black text-2xl text-slate-900 leading-tight tracking-tight uppercase">
                        Network <span class="text-blue-600">Gateways</span>
                    </h2>
                    <p class="text-xs font-black text-slate-500 uppercase tracking-widest mt-1">IP Verification & Access Control</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="px-4 py-2 bg-slate-900 rounded-xl flex items-center gap-2 shadow-lg shadow-slate-200">
                    <div class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-pulse"></div>
                    <span class="text-[11px] font-black text-white uppercase tracking-widest">Shield Active</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-10" x-data="{ 
        editingIp: null,
        showEditModal: false,
        openEdit(ip) {
            this.editingIp = ip;
            this.showEditModal = true;
            $nextTick(() => {
                const form = document.getElementById('edit-ip-form');
                form.action = `/settings/allowed-ips/${ip.id}`;
                document.getElementById('edit_ip_address').value = ip.ip_address;
                document.getElementById('edit_label').value = ip.label;
                document.getElementById('edit_location').value = ip.location || '';
                document.getElementById('edit_description').value = ip.description || '';
                document.getElementById('edit_is_active').checked = !!ip.is_active;
            });
        }
    }">
        <div class="w-full">
            @if(session('success'))
                <div class="mb-8 p-4 bg-emerald-500 text-white text-xs font-black uppercase tracking-widest rounded-2xl shadow-lg shadow-emerald-100 flex items-center gap-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start w-full mx-auto max-w-[1920px]">
                <!-- IP Configuration Panel -->
                <div class="md:col-span-12 lg:col-span-4 space-y-8 w-full">
                    <!-- Current Vector Card -->
                    <div class="bg-slate-900 rounded-3xl p-8 shadow-2xl relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-600/20 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
                        <h3 class="text-xs font-black text-blue-400 uppercase tracking-widest mb-4 font-sans">Origin Vector</h3>
                        <div class="flex flex-col gap-1 relative z-10 w-full">
                            <span class="text-3xl font-black text-white tracking-tighter break-all">{{ $currentIp }}</span>
                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                @if(!$isCurrentIpAllowed)
                                    <span class="w-2 h-2 bg-amber-500 rounded-full"></span>
                                    <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Unregistered Origin</span>
                                    <form action="{{ route('settings.allowed-ips.add-current') }}" method="POST" class="inline-block ml-auto">
                                        @csrf
                                        <button type="submit" class="text-[11px] font-black text-blue-400 hover:text-white uppercase tracking-widest underline decoration-2 underline-offset-4 transition-colors">
                                            Authorize
                                        </button>
                                    </form>
                                @else
                                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                                    <span class="text-[11px] font-black text-emerald-400 uppercase tracking-widest">Verified Connection</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Add Entry Card -->
                    <div class="bg-white/40 backdrop-blur-xl border border-white/60 p-8 rounded-3xl shadow-sm">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-10 h-10 bg-slate-900 rounded-2xl flex items-center justify-center shadow-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">New Gateway</h3>
                        </div>

                        <form action="{{ route('settings.allowed-ips.store') }}" method="POST" class="space-y-6">
                            @csrf
                            <div>
                                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">IP Address</label>
                                <input type="text" name="ip_address" required placeholder="0.0.0.0"
                                    class="w-full bg-white/50 border-white/60 rounded-2xl px-5 py-4 text-base font-black text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-mono uppercase">
                                @error('ip_address') <span class="text-red-500 text-xs font-bold uppercase mt-1 ml-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Label</label>
                                <input type="text" name="label" required placeholder="OFFICE ROUTER"
                                    class="w-full bg-white/50 border-white/60 rounded-2xl px-5 py-4 text-base font-black text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all uppercase">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Location</label>
                                    <input type="text" name="location" placeholder="HQ"
                                        class="w-full bg-white/50 border-white/60 rounded-2xl px-4 py-3 text-sm font-black text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all uppercase">
                                </div>
                                <div class="flex items-end pb-3">
                                    <label class="flex items-center cursor-pointer group">
                                        <input type="checkbox" name="is_active" value="1" checked class="w-5 h-5 rounded-lg border-white/60 bg-white/40 text-blue-600 focus:ring-blue-500/20 transition-all shadow-sm">
                                        <span class="ml-3 text-xs font-black text-slate-500 uppercase tracking-widest group-hover:text-slate-900">Active</span>
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="w-full py-5 bg-slate-900 text-white text-sm font-black uppercase tracking-widest rounded-2xl hover:bg-blue-600 transition-all duration-300 shadow-xl shadow-slate-200">
                                Deploy Gateway
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Verified Gateways List -->
                <div class="md:col-span-12 lg:col-span-8 w-full">
                    <div class="bg-white/40 backdrop-blur-xl border border-white/60 rounded-3xl shadow-sm overflow-hidden">
                        <div class="p-8 border-b border-white/60 bg-white/20">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-blue-600/10 rounded-2xl flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Verified Gateways</h3>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-0.5">{{ $allowedIps->total() }} ACTIVE NODES</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="w-full overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-[600px]">
                                <thead>
                                    <tr class="bg-slate-900/5">
                                        <th class="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/20">Address Vector</th>
                                        <th class="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/20">Identification</th>
                                        <th class="px-8 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/20">Status</th>
                                        <th class="px-8 py-5 text-right text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/20">Operations</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/60">
                                    @forelse($allowedIps as $ip)
                                        <tr class="group hover:bg-white/40 transition-all duration-300 {{ !$ip->is_active ? 'opacity-40' : '' }}">
                                            <td class="px-8 py-6 whitespace-nowrap">
                                                <div class="flex items-center gap-3">
                                                    <span class="px-3 py-1.5 bg-slate-900 rounded-lg text-[11px] font-black text-white tracking-widest font-mono">{{ $ip->ip_address }}</span>
                                                    @if($ip->ip_address == $currentIp)
                                                        <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse shadow-[0_0_10px_rgba(59,130,246,0.5)]" title="Your current IP address"></div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-8 py-6 whitespace-nowrap">
                                                <div class="text-[10px] font-black text-slate-900 uppercase tracking-widest truncate max-w-[150px]">{{ $ip->label }}</div>
                                                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ $ip->location ?: "UNSPECIFIED LOC" }}</div>
                                            </td>
                                            <td class="px-8 py-6 text-center whitespace-nowrap">
                                                <form action="{{ route('settings.allowed-ips.toggle', $ip) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest border transition-all
                                                        {{ $ip->is_active ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600 hover:bg-emerald-500 hover:text-white' : 'bg-slate-500/10 border-slate-500/20 text-slate-600 hover:bg-slate-50 hover:text-white' }}">
                                                        {{ $ip->is_active ? 'Shield On' : 'Shield Off' }}
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="px-8 py-6 text-right whitespace-nowrap">
                                                <div class="flex justify-end items-center gap-2">
                                                    <button @click="openEdit({{ json_encode($ip) }})" class="p-2.5 bg-blue-600/10 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all transform hover:scale-110">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                    </button>
                                                    <form action="{{ route('settings.allowed-ips.destroy', $ip) }}" method="POST" class="inline" onsubmit="return confirm('Terminate this gateway node?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-2.5 bg-rose-600/10 text-rose-600 rounded-xl hover:bg-rose-600 hover:text-white transition-all transform hover:scale-110">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-8 py-20 text-center">
                                                <div class="flex flex-col items-center gap-4">
                                                    <div class="w-20 h-20 bg-slate-900/5 rounded-full flex items-center justify-center">
                                                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    </div>
                                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">No gateway vectors registered</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($allowedIps->hasPages())
                            <div class="p-8 border-t border-white/60">
                                {{ $allowedIps->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal Redesigned -->
        <div x-cloak x-show="showEditModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 flex items-center justify-center p-4">
            
            <div x-show="showEditModal" @click="showEditModal = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity"></div>
            
            <div x-show="showEditModal" class="relative bg-white/90 backdrop-blur-2xl border border-white w-full max-w-md rounded-[2.5rem] shadow-2xl overflow-hidden">
                <div class="px-8 py-10">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <h3 class="text-lg font-black text-slate-900 uppercase tracking-widest">Edit Node</h3>
                    </div>

                    <form id="edit-ip-form" method="POST" class="space-y-6">
                        @csrf
                        @method("PUT")
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2 ml-1">IP Address</label>
                            <input type="text" name="ip_address" id="edit_ip_address" required class="w-full bg-white/50 border-white/60 rounded-2xl px-5 py-4 text-sm font-black text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-mono uppercase">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2 ml-1">Label</label>
                            <input type="text" name="label" id="edit_label" required class="w-full bg-white/50 border-white/60 rounded-2xl px-5 py-4 text-sm font-black text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all uppercase">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2 ml-1">Location</label>
                            <input type="text" name="location" id="edit_location" class="w-full bg-white/50 border-white/60 rounded-2xl px-5 py-4 text-sm font-black text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all uppercase">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2 ml-1">Description</label>
                            <textarea name="description" id="edit_description" rows="2" class="w-full bg-white/50 border-white/60 rounded-2xl px-5 py-4 text-sm font-black text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all uppercase"></textarea>
                        </div>
                        <label class="flex items-center cursor-pointer group p-4 bg-slate-900/5 rounded-2xl border border-slate-900/10 hover:bg-slate-900/10 transition-all">
                            <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="w-6 h-6 rounded-lg border-white text-blue-600 focus:ring-blue-500/20 transition-all">
                            <span class="ml-4 text-[10px] font-black text-slate-900 uppercase tracking-widest group-hover:text-blue-600">Active Node Status</span>
                        </label>
                        
                        <div class="flex gap-4 pt-4">
                            <button type="button" @click="showEditModal = false" class="flex-1 py-5 border border-slate-200 text-slate-500 text-[11px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-slate-50 transition-all">Cancel</button>
                            <button type="submit" class="flex-1 py-5 bg-slate-900 text-white text-[11px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-blue-600 transition-all shadow-xl shadow-slate-200">Save Node</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
