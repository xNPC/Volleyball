@section('title', $document?->title ?? 'Согласие на обработку персональных данных')
@section('description', 'Согласие на обработку персональных данных пользователей портала волейбольных турниров.')
@section('og_type', 'website')
@section('og_title', $document?->title ?? 'Согласие на обработку персональных данных')
@section('canonical', request()->url())
@php $canonicalUrl = request()->url(); @endphp

<x-app-layout>
    <div class="mx-auto max-w-3xl">
        <div class="mb-8 text-center">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-accent-500/10 text-accent-500">
                @svg('lucide-file-check-2', 'h-7 w-7')
            </div>
            <h1 class="font-display text-3xl font-bold tracking-tight text-brand-800 sm:text-4xl">
                {{ $document?->title ?? 'Согласие на обработку персональных данных' }}
            </h1>
            <p class="mt-2 text-slate-500">Обработка персональных данных на портале волейбольных турниров</p>
        </div>

        <x-card class="p-6 sm:p-8">
            @if ($document?->content)
                <div class="prose-sm max-w-none space-y-6 text-sm leading-relaxed text-slate-600 [&_h2]:font-display [&_h2]:text-base [&_h2]:font-bold [&_h2]:text-brand-800 [&_h2]:mt-6 [&_h2]:mb-2 [&_p]:mt-2 [&_ul]:mt-2 [&_ul]:list-disc [&_ul]:ps-5 [&_ul]:space-y-1">
                    {!! $document->content !!}
                </div>
            @else
                <p class="text-sm leading-relaxed text-slate-600">Текст документа скоро будет опубликован.</p>
            @endif
        </x-card>

        <p class="mt-6 text-center text-sm text-slate-500">
            Дополнительно ознакомьтесь с
            <a href="{{ route('privacy') }}" class="font-medium text-accent-600 transition hover:text-accent-700">политикой конфиденциальности</a>.
        </p>

        @auth
            @if (!auth()->user()->personal_data_consent_at)
                <x-card class="mt-6 p-6 sm:p-8">
                    <x-validation-errors class="mb-4" />

                    <form method="POST" action="{{ route('legal.consent.accept') }}">
                        @csrf

                        <div class="flex items-start">
                            <x-checkbox name="personal_data_consent_confirm" id="personal_data_consent_confirm" required class="mt-1" />
                            <div class="ms-2 text-sm text-slate-600">
                                Я подтверждаю, что ознакомился с документом выше и даю согласие на обработку моих персональных данных на портале волейбольных турниров.
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <x-btn type="submit" variant="primary">
                                Принять согласие
                            </x-btn>
                        </div>
                    </form>
                </x-card>
            @else
                <p class="mt-6 text-center text-sm text-slate-500">
                    Согласие подписано {{ auth()->user()->personal_data_consent_at->format('d.m.Y H:i') }}.
                </p>
            @endif
        @endauth
    </div>
</x-app-layout>
