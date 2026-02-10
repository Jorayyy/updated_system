<x-app-layout>
    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 px-4">
            <!-- Header -->
            <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <nav class="flex mb-2" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{ route('audit-logs.index') }}" class="text-[10px] font-bold text-gray-400 hover:text-indigo-600 uppercase tracking-[0.2em] transition-colors flex items-center">
                                    <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                    Back to Activity Logs
                                </a>
                            </li>
                        </ol>
                    </nav>
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Log Details</h2>
                    <p class="text-gray-500 mt-1 text-sm bg-indigo-50 px-3 py-1 rounded-lg inline-block font-medium border border-indigo-100">
                        Reference ID: <span class="text-indigo-600 font-bold">#{{ $auditLog->id }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="px-4 py-3 bg-white rounded-2xl shadow-sm border border-gray-100 flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-indigo-500 animate-ping"></div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">System Audit Record</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Context -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100">
                            <h3 class="text-xs font-extrabold text-gray-400 uppercase tracking-widest">Metadata</h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 tracking-widest">Performed By</label>
                                <div class="flex items-center bg-gray-50 p-4 rounded-2xl border border-gray-100 hover:border-indigo-200 transition-colors group">
                                    <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-lg group-hover:scale-110 transition-transform">
                                        {{ substr($auditLog->user?->name ?? 'S', 0, 1) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $auditLog->user?->name ?? 'System Process' }}</div>
                                        <div class="text-[11px] text-gray-500 font-medium">{{ $auditLog->user?->email ?? 'no-reply@system.com' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 tracking-widest">Activity</label>
                                    <div class="text-xs font-bold text-indigo-600 px-3 py-2 bg-indigo-50 rounded-xl border border-indigo-100 text-center uppercase">
                                        {{ str_replace('_', ' ', $auditLog->action) }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 tracking-widest">IP Tracking</label>
                                    <div class="text-xs font-mono font-bold text-gray-600 px-3 py-2 bg-gray-50 rounded-xl border border-gray-100 text-center">
                                        {{ $auditLog->ip_address ?? '0.0.0.0' }}
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 tracking-widest">Timestamp (Local)</label>
                                <div class="text-xs font-bold text-gray-700 px-4 py-3 bg-gray-50 rounded-xl border border-gray-100 shadow-inner flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $auditLog->created_at->format('M d, Y • h:i:s A') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100">
                            <h3 class="text-xs font-extrabold text-gray-400 uppercase tracking-widest">Target Asset</h3>
                        </div>
                        <div class="p-6">
                            <div class="bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-800">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="px-2 py-1 bg-indigo-500 text-white text-[9px] font-black rounded uppercase tracking-tighter">Model Hook</div>
                                    <span class="text-[10px] font-bold text-slate-500 font-mono">ID: {{ $auditLog->model_id ?? 'N/A' }}</span>
                                </div>
                                <div class="text-xs font-mono text-indigo-300 truncate tracking-tight">{{ $auditLog->model_type }}</div>
                            </div>
                            <div class="mt-6">
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 tracking-widest">Event Summary</label>
                                <blockquote class="relative p-4 text-sm font-bold text-gray-600 bg-gray-50 rounded-2xl border-l-4 border-indigo-500">
                                    "{{ $auditLog->description ?? 'No detailed description logged for this event.' }}"
                                </blockquote>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Data & Changes -->
                <div class="lg:col-span-2 space-y-6">
                    @if($auditLog->old_values || $auditLog->new_values)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-xs font-extrabold text-gray-400 uppercase tracking-widest">Field Comparison</h3>
                            <div class="flex items-center gap-1.5 px-2 py-1 bg-white border border-gray-200 rounded-lg text-[10px] font-bold text-gray-500 uppercase">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Live Changes
                            </div>
                        </div>
                        <div class="p-0 overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-50/30">
                                        <th class="px-6 py-4 text-left text-[10px] font-extrabold text-gray-400 uppercase tracking-widest border-b border-gray-100">Property</th>
                                        <th class="px-6 py-4 text-left text-[10px] font-extrabold text-rose-500 uppercase tracking-widest border-b border-gray-100 italic">Original State</th>
                                        <th class="px-6 py-4 text-left text-[10px] font-extrabold text-emerald-600 uppercase tracking-widest border-b border-gray-100">Updated State</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 font-medium">
                                    @php
                                        // Ensure we're working with arrays, even if casting fails or data is double-encoded
                                        $oldVals = is_array($auditLog->old_values) ? $auditLog->old_values : json_decode($auditLog->old_values, true) ?? [];
                                        $newVals = is_array($auditLog->new_values) ? $auditLog->new_values : json_decode($auditLog->new_values, true) ?? [];

                                        $allKeys = array_unique(array_merge(
                                            array_keys($oldVals),
                                            array_keys($newVals)
                                        ));
                                        
                                        $prettyValue = function($v) {
                                            if (is_null($v)) return '<span class="text-gray-300 italic">null</span>';
                                            if (is_bool($v)) return $v ? 'TRUE' : 'FALSE';
                                            if (is_array($v)) return '<pre class="text-[10px]">'.json_encode($v, JSON_PRETTY_PRINT).'</pre>';
                                            return $v;
                                        };
                                    @endphp
                                    @foreach($allKeys as $key)
                                        @php
                                            $old = $oldVals[$key] ?? null;
                                            $new = $newVals[$key] ?? null;
                                            $isDifferent = $old !== $new;
                                        @endphp
                                        <tr class="hover:bg-gray-50/50 transition-colors group">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-2">
                                                    @if($isDifferent)
                                                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div>
                                                    @endif
                                                    <span class="text-xs font-bold text-gray-500 font-mono tracking-tighter">{{ $key }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-xs text-gray-400 bg-gray-50/50 p-2.5 rounded-xl border border-gray-100 min-h-[36px] break-all leading-relaxed">
                                                    {!! $prettyValue($old) !!}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-xs {{ $isDifferent ? 'text-emerald-700 bg-emerald-50/50 border-emerald-100' : 'text-gray-500 bg-gray-50 border-gray-100' }} p-2.5 rounded-xl border min-h-[36px] font-bold break-all leading-relaxed shadow-sm">
                                                    {!! $prettyValue($new) !!}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @else
                    <div class="bg-white rounded-3xl border border-gray-100 p-16 text-center shadow-sm">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-2">Non-Data Modifying Event</h4>
                        <p class="text-gray-400 text-sm max-w-sm mx-auto">This log record indicates an action that didn't change entity fields (e.g., viewing, deleting, or authentication).</p>
                    </div>
                    @endif

                    <!-- System Diagnostics -->
                    <div x-data="{ open: false }" class="bg-slate-900 rounded-3xl overflow-hidden shadow-2xl border border-slate-800">
                        <button @click="open = !open" class="w-full px-8 py-5 flex items-center justify-between hover:bg-slate-800 transition-colors group">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center group-hover:bg-indigo-600 transition-colors">
                                    <svg class="w-5 h-5 text-indigo-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <div class="text-left">
                                    <span class="block text-sm font-bold text-white uppercase tracking-widest">Diagnostic Raw JSON</span>
                                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-tighter">Technical audit trace output</span>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-slate-600 transition-transform duration-500" :class="open ? 'rotate-180 text-white' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8 border-t border-slate-800 bg-slate-900/50">
                            <div class="relative">
                                <div class="absolute top-0 right-0 px-2 py-1 bg-slate-800 rounded text-[9px] font-bold text-slate-500 uppercase tracking-tighter">application/json</div>
                                <pre class="text-[11px] leading-relaxed text-indigo-300 font-mono overflow-x-auto whitespace-pre-wrap">@json($auditLog, JSON_PRETTY_PRINT)</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

