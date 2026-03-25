@extends("layouts.app")

@section("header")
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 py-8">
        <div>
            <h1 class="text-3xl font-black text-rose-800 tracking-tighter uppercase mb-1">DTR Dashboard</h1>
            <p class="text-rose-600 font-bold text-xs uppercase tracking-widest flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                Daily Time Record Management System
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="bg-white/80 backdrop-blur border border-rose-100 px-4 py-2 rounded shadow-sm text-right">
                <div class="text-[10px] font-black text-rose-400 uppercase leading-none mb-1">Current Date</div>
                <div class="text-sm font-black text-rose-800">{{ now()->format("F d, Y") }}</div>
            </div>
            @if(request("payroll_group_id"))
                <a href="{{ route("dtr-approval.index") }}" class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-2.5 rounded shadow-sm hover:bg-rose-100 transition-all text-xs font-black uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M11 15l-3-3m0 0l3-3m-3 3h8m-1 7a9 9 0 110-18 9 9 0 010 18z"></path></svg>
                    Back to Groups
                </a>
            @endif
        </div>
    </div>
@endsection

@section("content")
<div class="max-w-7xl mx-auto px-4 pb-12">
    @if(session("success"))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-6 rounded shadow-sm animate-bounce-in">
            <div class="flex items-center gap-3 italic text-emerald-800 font-bold text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session("success") }}
            </div>
        </div>
    @endif

    @if(!request("payroll_group_id"))
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($payrollGroups as $group)
                <div class="bg-white rounded shadow-sm border border-rose-200 overflow-hidden group hover:shadow-md transition-all">
                    <div class="bg-rose-100 px-4 py-2 border-b border-rose-200 flex justify-between items-center relative overflow-hidden">
                        <div class="absolute inset-y-0 left-0 w-1 bg-rose-500"></div>
                        <h3 class="text-[11px] font-black text-rose-800 uppercase tracking-wider pl-2">{{ $group->name }}</h3>
                        <span class="text-[9px] bg-white text-rose-600 px-1.5 py-0.5 rounded font-black uppercase border border-rose-100 shadow-sm">
                            {{ $group->period_type }}
                        </span>
                    </div>

                    <div class="p-5">
                        <form action="{{ route("dtr-approval.index") }}" method="GET" class="space-y-4">
                            <input type="hidden" name="payroll_group_id" value="{{ $group->id }}">
                            <div class="flex items-center gap-4">
                                <label class="w-24 text-[10px] font-black text-rose-600 uppercase text-right tracking-tight">Option</label>
                                <div class="flex-1 relative">
                                    <select name="status" class="w-full bg-white border border-slate-200 text-[11px] rounded p-1.5 focus:ring-1 focus:ring-rose-500 font-bold text-slate-700 appearance-none">
                                        <option value="pending" {{ request("status") == "pending" ? "selected" : "" }}>View Processed DTR</option>
                                        <option value="process">Process DTR</option>
                                        <option value="correction_pending">Check DTR Status</option>
                                        <option value="clear">Clear DTR</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <label class="w-24 text-[10px] font-black text-rose-600 uppercase text-right tracking-tight">Period</label>
                                <div class="flex-1 relative">
                                    <select name="payroll_period_id" id="period_select_{{ $group->id }}" class="w-full bg-rose-50/30 border border-slate-200 text-[11px] rounded p-1.5 focus:ring-1 focus:ring-rose-500 font-medium text-slate-700 appearance-none">
                                        @forelse($payrollPeriods->where("payroll_group_id", $group->id) as $period)
                                            <option value="{{ $period->id }}" {{ request("payroll_period_id") == $period->id ? "selected" : "" }}>
                                                {{ $period->remarks ?? optional($period->start_date)->format("M d") . " - " . optional($period->end_date)->format("M d, Y") }}
                                            </option>
                                        @empty
                                            <option value="">No Active Periods</option>
                                        @endforelse
                                    </select>
                                </div>
                            </div>

                            <div class="flex justify-end pt-4 mt-2 border-t border-slate-50">
                                <button type="submit" class="bg-rose-600 text-white font-black py-2.5 px-8 rounded shadow-md transition-all hover:bg-rose-700 active:scale-95 uppercase tracking-widest text-[11px] flex items-center gap-2 border-0">
                                    Generate
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 text-center">
                    <h2 class="text-xl font-black text-rose-800 uppercase tracking-tight">No Payroll Groups Found</h2>
                </div>
            @endforelse
        </div>
    @else
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('dtr-approval.index') }}" class="bg-white border border-rose-200 text-rose-600 px-4 py-2 rounded text-[10px] font-black uppercase tracking-widest hover:bg-rose-50 transition-all flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Groups
                </a>
                <h2 class="text-xs font-black text-rose-600 uppercase tracking-[0.2em]">Processing {{ $dtrs->total() }} Record(s)</h2>
            </div>
            <button onclick="approveBatch()" class="bg-emerald-600 text-white font-black px-4 py-2 rounded text-[10px] uppercase tracking-wider hover:bg-emerald-700 shadow-sm flex items-center gap-1.5 transition-all">
                Batch Approve
            </button>
        </div>

        <div class="bg-white rounded shadow-sm border border-rose-100 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-rose-50 border-b border-rose-100">
                        <tr>
                            <th class="px-6 py-4 w-10"><input type="checkbox" id="select-all" class="rounded border-rose-300 text-rose-600"></th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-rose-700">Employee</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-rose-700">Date</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-rose-700">Punches</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-rose-700">Net Work</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-rose-700 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($dtrs as $dtr)
                            <tr class="hover:bg-rose-50/50" id="dtr-row-{{ $dtr->id }}">
                                <td class="px-6 py-4"><input type="checkbox" value="{{ $dtr->id }}" class="dtr-checkbox rounded border-slate-200 text-rose-600"></td>
                                <td class="px-6 py-4 font-black text-xs text-slate-800 uppercase">{{ $dtr->employee->full_name }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-black text-slate-700">{{ $dtr->date->format("M d, Y") }}</div>
                                    <div class="text-[9px] font-bold text-rose-400 uppercase">{{ $dtr->date->format("l") }}</div>
                                </td>
                                <td class="px-6 py-4 text-[10px] font-bold text-slate-500">
                                    IN: {{ $dtr->clock_in ? $dtr->clock_in->format("h:i A") : "--:--" }} | OUT: {{ $dtr->clock_out ? $dtr->clock_out->format("h:i A") : "--:--" }}
                                </td>
                                <td class="px-6 py-4 font-black text-xs text-slate-800">{{ number_format($dtr->net_work_minutes / 60, 2) }} HRS</td>
                                <td class="px-6 py-4 text-right">
                                    <button onclick="approveDtr('{{ $dtr->id }}')" class="bg-rose-600 text-white font-black px-3 py-1.5 rounded text-[9px] uppercase hover:bg-rose-700">Approve</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-20 text-center uppercase text-[10px] font-black text-rose-300">No records found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<script>
    function approveDtr(id) {
        fetch("{{ url("dtr-approval") }}/" + id + "/approve", {
            method: "POST",
            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }
        }).then(r => r.json()).then(d => { if(d.success) { document.getElementById("dtr-row-" + id).remove(); } });
    }
    function approveBatch() {
        const ids = Array.from(document.querySelectorAll(".dtr-checkbox:checked")).map(cb => cb.value);
        if (ids.length === 0) return;
        fetch("{{ route("dtr-approval.bulk-approve") }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ dtr_ids: ids })
        }).then(r => r.json()).then(d => { if(d.success) window.location.reload(); });
    }
    document.getElementById("select-all")?.addEventListener("change", function() {
        document.querySelectorAll(".dtr-checkbox").forEach(cb => cb.checked = this.checked);
    });
</script>
@endsection
