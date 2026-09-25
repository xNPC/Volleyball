@props(['label' => ''])

<div class="text-center">
    <div {{ $attributes->merge(['class' => 'font-display text-2xl font-bold text-accent-500']) }}>
        {{ $slot }}
    </div>
    @if ($label)
        <div class="mt-0.5 text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</div>
    @endif
</div>