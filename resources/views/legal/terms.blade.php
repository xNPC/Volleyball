@section('title', $document?->title ?? 'Пользовательское соглашение')
@section('description', 'Пользовательское соглашение портала волейбольных турниров — правила использования сайта и участия в турнирах.')
@section('og_type', 'website')
@section('og_title', $document?->title ?? 'Пользовательское соглашение')
@section('canonical', request()->url())
@php $canonicalUrl = request()->url(); @endphp

<x-app-layout>
    <div class="mx-auto max-w-3xl">
        <div class="mb-8 text-center">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-accent-500/10 text-accent-500">
                @svg('lucide-file-text', 'h-7 w-7')
            </div>
            <h1 class="font-display text-3xl font-bold tracking-tight text-brand-800 sm:text-4xl">
                {{ $document?->title ?? 'Пользовательское соглашение' }}
            </h1>
            <p class="mt-2 text-slate-500">Правила использования портала волейбольных турниров</p>
        </div>

        <x-card class="p-6 sm:p-8">
            @if ($document?->content)
                <div class="prose-sm max-w-none space-y-6 text-sm leading-relaxed text-slate-600 [&_h2]:font-display [&_h2]:text-base [&_h2]:font-bold [&_h2]:text-brand-800 [&_h2]:mt-6 [&_h2]:mb-2 [&_p]:mt-2 [&_ul]:mt-2 [&_ul]:list-disc [&_ul]:ps-5 [&_ul]:space-y-1 [&_a]:font-medium [&_a]:text-accent-600 [&_a]:hover:text-accent-700">
                    {!! $document->content !!}
                </div>
                <p class="mt-6 border-t border-slate-100 pt-4 text-xs text-slate-400">
                    Редакция документа от {{ $document->updated_at->format('d.m.Y H:i') }}
                </p>
            @else
                <p class="text-sm leading-relaxed text-slate-600">Текст документа скоро будет опубликован.</p>
            @endif
        </x-card>

        <p class="mt-6 text-center text-sm text-slate-500">
            Дополнительно ознакомьтесь с
            <a href="{{ route('privacy') }}" class="font-medium text-accent-600 transition hover:text-accent-700">политикой конфиденциальности</a>
            и
            <a href="{{ route('legal.consent') }}" class="font-medium text-accent-600 transition hover:text-accent-700">согласием на обработку персональных данных</a>.
        </p>
    </div>
</x-app-layout>
