@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-black text-xs text-slate-700 uppercase tracking-widest mb-1']) }}>
    {{ $value ?? $slot }}
</label>
