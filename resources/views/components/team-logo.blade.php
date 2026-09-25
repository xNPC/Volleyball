@props(['name' => '', 'size' => 'md'])

@php
    $sizes = [
        'sm' => 'h-10 w-10 text-sm',
        'md' => 'h-14 w-14 text-lg',
        'lg' => 'h-20 w-20 text-2xl',
        'xl' => 'h-24 w-24 text-3xl',
    ];
    $initials = \Illuminate\Support\Str::of($name)->trim()->substr(0, 2)->upper();
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand-600 to-brand-800 font-bold text-white ' . $sizes[$size]]) }}>
    {{ $initials }}
</span>