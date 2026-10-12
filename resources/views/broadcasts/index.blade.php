@section('title', 'Трансляции')
@section('description', 'Прямые трансляции волейбольных матчей и анонсы предстоящих эфиров.')
@section('og_type', 'website')
@section('og_title', 'Трансляции')
@section('canonical', request()->url())
@php $canonicalUrl = request()->url(); @endphp

<x-app-layout>
    <div>
        <x-breadcrumb :items="[['label' => 'Трансляции']]" />

        <div class="mb-8 text-center">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-accent-500/10 text-accent-500">
                @svg('lucide-radio', 'h-7 w-7')
            </div>
            <h1 class="font-display text-3xl font-bold tracking-tight text-brand-800 sm:text-4xl">Трансляции</h1>
            <p class="mt-2 text-slate-500">Прямые эфиры и анонсы матчей</p>
        </div>

        @if ($live->isNotEmpty())
            <section class="mb-10">
                <h2 class="mb-4 flex items-center gap-2 font-display text-xl font-bold text-brand-800">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-500 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-600"></span>
                    </span>
                    Сейчас в эфире
                </h2>

                <div class="space-y-6">
                    @foreach ($live as $broadcast)
                        <x-card class="overflow-hidden">
                            @include('partials.broadcast-player', ['broadcast' => $broadcast])

                            <div class="p-5">
                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                    <x-badge variant="red">
                                        @svg('lucide-radio', 'h-3.5 w-3.5')В эфире
                                    </x-badge>
                                    @if ($broadcast->tournament)
                                        <x-badge variant="blue">{{ $broadcast->tournament->name }}</x-badge>
                                    @endif
                                </div>

                                <h3 class="font-display text-lg font-bold text-brand-800">{{ $broadcast->title }}</h3>

                                @include('partials.broadcast-teams', ['broadcast' => $broadcast])

                                @if ($broadcast->description)
                                    <p class="mt-3 text-sm text-slate-500">{{ $broadcast->description }}</p>
                                @endif

                                <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-500">
                                    @if ($broadcast->game && $broadcast->game->venue)
                                        <span class="inline-flex items-center gap-1">
                                            @svg('lucide-map-pin', 'h-3.5 w-3.5'){{ $broadcast->game->venue->name }}
                                        </span>
                                    @endif
                                    @if ($broadcast->scheduled_at)
                                        <span class="inline-flex items-center gap-1">
                                            @svg('lucide-clock', 'h-3.5 w-3.5'){{ $broadcast->scheduled_at->format('d.m.Y H:i') }}
                                        </span>
                                    @endif
                                    <a href="{{ route('broadcasts.show', $broadcast) }}"
                                       class="inline-flex items-center gap-1 font-medium text-accent-600 transition hover:text-accent-700">
                                        @svg('lucide-external-link', 'h-3.5 w-3.5')Открыть страницу трансляции
                                    </a>
                                </div>
                            </div>
                        </x-card>
                    @endforeach
                </div>
            </section>
        @endif

        <section>
            <h2 class="mb-5 flex items-center gap-2 font-display text-xl font-bold text-brand-800">
                @svg('lucide-calendar', 'h-5 w-5 text-accent-500')Скоро
            </h2>

            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($upcoming as $broadcast)
                    <x-card hoverable class="flex h-full flex-col overflow-hidden">
                        <a href="{{ route('broadcasts.show', $broadcast) }}" class="relative block">
                            @if ($broadcast->preview_url)
                                <img src="{{ $broadcast->preview_url }}"
                                     alt="{{ $broadcast->title }}"
                                     class="aspect-video w-full object-cover transition duration-300 hover:scale-105">
                            @else
                                <div class="flex aspect-video w-full items-center justify-center bg-gradient-to-br from-brand-700 to-brand-900 text-white/80">
                                    @svg('lucide-tv', 'h-10 w-10')
                                </div>
                            @endif
                            <div class="absolute right-3 top-3">
                                <x-badge variant="amber">Скоро</x-badge>
                            </div>
                        </a>
                        <div class="flex grow flex-col p-5">
                            @if ($broadcast->tournament)
                                <span class="mb-1 text-xs font-semibold uppercase tracking-wide text-accent-600">{{ $broadcast->tournament->name }}</span>
                            @endif
                            <a href="{{ route('broadcasts.show', $broadcast) }}"
                               class="font-display text-lg font-bold text-brand-800 transition hover:text-accent-600">
                                {{ $broadcast->title }}
                            </a>
                            @if ($broadcast->description)
                                <p class="mt-1 flex-1 text-sm text-slate-500">{{ Str::limit($broadcast->description, 100) }}</p>
                            @endif
                            <div class="mt-4 flex items-center gap-1 text-xs text-slate-500">
                                @svg('lucide-clock', 'h-3.5 w-3.5')
                                {{ $broadcast->scheduled_at ? $broadcast->scheduled_at->format('d.m.Y H:i') : 'Дата уточняется' }}
                            </div>
                        </div>
                    </x-card>
                @empty
                    @if ($live->isEmpty())
                        <x-card class="col-span-full p-8 text-center text-slate-500">
                            @svg('lucide-info', 'mx-auto mb-2 h-6 w-6 text-slate-400')
                            Трансляций пока нет
                        </x-card>
                    @else
                        <x-card class="col-span-full p-6 text-center text-sm text-slate-500">
                            @svg('lucide-info', 'mx-auto mb-2 h-6 w-6 text-slate-400')
                            Предстоящих трансляций пока не запланировано
                        </x-card>
                    @endif
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
