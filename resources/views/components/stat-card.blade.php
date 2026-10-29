@props(['label', 'value', 'hint' => null])

<div {{ $attributes->class(['card']) }}>
    <div class="flex items-start justify-between gap-3">
        <p class="text-sm font-semibold text-ink-500">{{ $label }}</p>
        <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-brand-100 text-brand-700">
            {{ $icon }}
        </span>
    </div>
    <p class="mt-3 text-2xl font-extrabold tabular-nums tracking-tight">{{ $value }}</p>
    @isset($hint)
        <p class="mt-1 text-xs text-ink-500">{{ $hint }}</p>
    @endisset
</div>
