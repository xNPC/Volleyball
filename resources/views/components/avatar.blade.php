@props(['photo' => null, 'name' => '', 'size' => 'md'])

@php
    $sizes = [
        'xs' => 'h-6 w-6 text-[10px]',
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-12 w-12 text-base',
        'lg' => 'h-16 w-16 text-xl',
        'xl' => 'h-24 w-24 text-3xl',
    ];
    $initials = \Illuminate\Support\Str::of($name)->trim()->substr(0, 2)->upper();
@endphp

@if ($photo)
    <img src="{{ $photo }}" alt="{{ $name }}" {{ $attributes->merge(['class' => 'shrink-0 rounded-full object-cover ' . $sizes[$size]]) }}>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-700 font-bold text-white ' . $sizes[$size]]) }}>
        {{ $initials }}
    </span>
@endif