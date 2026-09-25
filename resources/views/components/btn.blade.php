@props(['variant' => 'primary', 'size' => 'md', 'href' => null])

@php
    $variants = [
        'primary' => 'inline-flex items-center justify-center gap-2 rounded-lg bg-accent-500 font-semibold text-white shadow-sm transition hover:bg-accent-600',
        'brand' => 'inline-flex items-center justify-center gap-2 rounded-lg bg-brand-700 font-semibold text-white shadow-sm transition hover:bg-brand-800',
        'outline-accent' => 'inline-flex items-center justify-center gap-2 rounded-lg border border-accent-300 bg-white font-semibold text-accent-600 transition hover:bg-accent-50',
        'outline' => 'inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white font-medium text-brand-700 transition hover:bg-slate-50 hover:text-brand-800',
        'white' => 'inline-flex items-center justify-center gap-2 rounded-lg bg-white font-semibold text-brand-800 shadow-sm transition hover:bg-brand-50',
        'ghost' => 'inline-flex items-center justify-center gap-2 rounded-lg font-medium text-brand-700 transition hover:bg-slate-100',
    ];
    $sizes = [
        'sm' => 'px-3.5 py-1.5 text-sm',
        'md' => 'px-5 py-2.5 text-sm',
        'lg' => 'px-7 py-3 text-base',
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $variants[$variant] . ' ' . $sizes[$size] . ' focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent-500/40 focus-visible:ring-offset-2']) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $variants[$variant] . ' ' . $sizes[$size] . ' focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent-500/40 focus-visible:ring-offset-2']) }}>
        {{ $slot }}
    </button>
@endif