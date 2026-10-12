@section('title', $broadcast->title)
@section('description', Str::limit(strip_tags($broadcast->description), 160) ?: 'Трансляция волейбольного матча.')
@section('og_type', 'video.other')
@section('og_title', $broadcast->title)
@section('canonical', request()->url())
@php $canonicalUrl = request()->url(); @endphp

<x-app-layout>
    <div>
        <x-breadcrumb :items="[
            ['label' => 'Трансляции', 'url' => route('broadcasts.index')],
            ['label' => $broadcast->title],
        ]" />

        <div class="mx-auto max-w-5xl">
            @if ($broadcast->is_live)
                <div class="mb-4 flex justify-center">
                    <x-badge variant="red" class="text-sm">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-white opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-white"></span>
                        </span>
                        Прямой эфир
                    </x-badge>
                </div>
            @elseif ($broadcast->status === 'scheduled')
                <div class="mb-4 flex justify-center">
                    <x-badge variant="amber" class="text-sm">Скоро</x-badge>
                </div>
            @endif

            @include('partials.broadcast-player', ['broadcast' => $broadcast])

            <div class="mt-6">
                <div class="flex flex-wrap items-center gap-2">
                    @if ($broadcast->tournament)
                        <x-badge variant="blue">{{ $broadcast->tournament->name }}</x-badge>
                    @endif
                </div>

                <h1 class="mt-2 font-display text-2xl font-bold tracking-tight text-brand-800 sm:text-3xl">{{ $broadcast->title }}</h1>

                @include('partials.broadcast-teams', ['broadcast' => $broadcast])

                @if ($broadcast->description)
                    <div class="mt-4 max-w-none text-sm text-slate-600">
                        {!! nl2br(e($broadcast->description)) !!}
                    </div>
                @endif

                <div class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-500">
                    @if ($broadcast->game && $broadcast->game->venue)
                        <span class="inline-flex items-center gap-1">
                            @svg('lucide-map-pin', 'h-4 w-4'){{ $broadcast->game->venue->name }}
                        </span>
                    @endif
                    @if ($broadcast->scheduled_at)
                        <span class="inline-flex items-center gap-1">
                            @svg('lucide-clock', 'h-4 w-4'){{ $broadcast->scheduled_at->format('d.m.Y H:i') }}
                        </span>
                    @endif
                </div>

                <div class="mt-8 border-t border-slate-100 pt-4">
                    <a href="{{ route('broadcasts.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 transition hover:text-accent-600">
                        @svg('lucide-arrow-left', 'h-4 w-4')Ко всем трансляциям
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
