@props(['variant' => 'slate'])

@php
    $variants = [
        'slate' => 'bg-slate-100 text-slate-700',
        'green' => 'bg-emerald-100 text-emerald-800',
        'red' => 'bg-red-100 text-red-700',
        'amber' => 'bg-amber-100 text-amber-800',
        'blue' => 'bg-brand-100 text-brand-700',
        'accent' => 'bg-accent-500 text-white',
        'dark' => 'bg-slate-800 text-white',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold ' . $variants[$variant]]) }}>
    {{ $slot }}
</span>