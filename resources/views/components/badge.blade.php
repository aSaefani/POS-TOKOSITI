@props(['tone' => 'neutral'])

<span {{ $attributes->class(['badge', 'bg-emerald-100 text-emerald-800' => $tone === 'success', 'bg-amber-100 text-amber-800' => $tone === 'warning', 'bg-rose-100 text-rose-800' => $tone === 'danger', 'bg-slate-200 text-slate-700' => $tone === 'neutral']) }}>
    {{ $slot }}
</span>
