@props(['items' => []])

<nav class="mb-5" aria-label="Хлебные крошки">
    <ol class="flex flex-wrap items-center gap-1.5 text-sm text-slate-500">
        <li>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1 transition hover:text-accent-500">
                @svg('lucide-home', 'h-3.5 w-3.5')Главная
            </a>
        </li>
        @foreach ($items as $item)
            <li class="flex items-center gap-1.5">
                <span class="text-slate-300">/</span>
                @if (!empty($item['url']))
                    <a href="{{ $item['url'] }}" class="transition hover:text-accent-500">{{ $item['label'] }}</a>
                @else
                    <span class="font-medium text-brand-800">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>