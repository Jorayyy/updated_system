<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-6 py-3 bg-slate-900 border border-transparent rounded-2xl font-black text-xs text-white uppercase tracking-widest hover:bg-slate-800 focus:bg-slate-800 active:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg']) }}>
    {{ $slot }}
</button>
