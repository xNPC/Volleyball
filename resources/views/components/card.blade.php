@props(['hoverable' => false])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200/70 bg-white shadow-card ' . ($hoverable ? 'transition hover:bg-slate-50/70' : '')]) }}>
    {{ $slot }}
</div>