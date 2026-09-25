@props(['model' => null, 'placeholder' => 'Поиск...'])

<div class="relative">
    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
        @svg('lucide-search', 'h-4 w-4')
    </span>
    <input
        type="text"
        placeholder="{{ $placeholder }}"
        @if ($model) wire:model.live="{{ $model }}" @endif
        {{ $attributes->merge(['class' => 'input-base py-2.5 pl-10']) }}
    >
</div>