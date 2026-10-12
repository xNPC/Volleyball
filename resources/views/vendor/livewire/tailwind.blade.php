@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Навигация по страницам" class="flex flex-col items-center justify-between gap-4 sm:flex-row">
            <p class="text-sm text-slate-500">
                Показано
                <span class="font-medium text-brand-700">{{ $paginator->firstItem() }}</span>
                –
                <span class="font-medium text-brand-700">{{ $paginator->lastItem() }}</span>
                из
                <span class="font-medium text-brand-700">{{ $paginator->total() }}</span>
            </p>

            <div class="flex items-center gap-1">
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="Назад"
                          class="inline-flex cursor-not-allowed items-center gap-1 rounded-lg border border-slate-100 bg-slate-50 px-3 py-1.5 text-sm font-medium text-slate-300">
                        @svg('lucide-chevron-left', 'h-4 w-4')Назад
                    </span>
                @else
                    <button type="button"
                            wire:click="previousPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                            wire:loading.attr="disabled"
                            aria-label="Назад"
                            class="inline-flex cursor-pointer items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-brand-700 transition hover:border-accent-300 hover:text-accent-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent-500/40">
                        @svg('lucide-chevron-left', 'h-4 w-4')Назад
                    </button>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-1 text-slate-400">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page"
                                          class="inline-flex min-w-[2.25rem] cursor-default items-center justify-center rounded-lg border border-accent-500 bg-accent-500 px-3 py-1.5 text-sm font-semibold text-white">
                                        {{ $page }}
                                    </span>
                                @else
                                    <button type="button"
                                            wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                            wire:loading.attr="disabled"
                                            aria-label="Перейти на страницу {{ $page }}"
                                            class="inline-flex min-w-[2.25rem] cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-brand-700 transition hover:border-accent-300 hover:text-accent-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent-500/40">
                                        {{ $page }}
                                    </button>
                                @endif
                            </span>
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <button type="button"
                            wire:click="nextPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                            wire:loading.attr="disabled"
                            aria-label="Вперёд"
                            class="inline-flex cursor-pointer items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-brand-700 transition hover:border-accent-300 hover:text-accent-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent-500/40">
                        Вперёд @svg('lucide-chevron-right', 'h-4 w-4')
                    </button>
                @else
                    <span aria-disabled="true" aria-label="Вперёд"
                          class="inline-flex cursor-not-allowed items-center gap-1 rounded-lg border border-slate-100 bg-slate-50 px-3 py-1.5 text-sm font-medium text-slate-300">
                        Вперёд @svg('lucide-chevron-right', 'h-4 w-4')
                    </span>
                @endif
            </div>
        </nav>
    @endif
</div>
