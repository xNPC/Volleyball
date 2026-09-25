<x-app-layout>
    <div>
        <x-breadcrumb :items="[
            ['label' => 'Турниры', 'url' => route('tournaments.index')],
            ['label' => $tournament->name],
        ]" />

        <x-card class="mb-8 p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <h1 class="font-display text-2xl font-bold tracking-tight text-brand-800 sm:text-3xl">
                        {{ $tournament->name }}
                    </h1>
                    @if ($tournament->description)
                        <p class="mt-2 text-slate-500">{{ $tournament->description }}</p>
                    @endif
                    <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1.5 text-sm text-slate-500">
                        <span class="inline-flex items-center gap-1.5">
                            @svg('lucide-calendar-days', 'h-4 w-4'){{ $tournament->start_date->format('d.m.Y') }} — @if ($tournament->end_date){{ $tournament->end_date->format('d.m.Y') }}@endif
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            @svg('lucide-layers', 'h-4 w-4')Этапов: {{ $tournament->stages->count() }}
                        </span>
                        @if ($tournament->volleyball_type)
                            <span class="inline-flex items-center gap-1.5">
                                @svg('lucide-volleyball', 'h-4 w-4'){{ $tournament->volleyball_type }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="shrink-0">
                    @php
                        $statusBadge = match ($tournament->status) {
                            'ongoing' => 'green',
                            'planned' => 'amber',
                            default => 'slate',
                        };
                    @endphp
                    <x-badge :variant="$statusBadge">
                        @svg('lucide-volleyball', 'h-3.5 w-3.5'){{ $tournament::STATUS[$tournament->status] ?? $tournament->status }}
                    </x-badge>
                </div>
            </div>
        </x-card>

        <h2 class="mb-4 flex items-center gap-2 font-display text-xl font-bold text-brand-800">
            @svg('lucide-layers', 'h-5 w-5 text-accent-500')Этапы турнира
        </h2>

        <div class="grid gap-5 md:grid-cols-2">
            @foreach ($tournament->stages as $stage)
                <x-card hoverable class="flex flex-col p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="break-words font-display text-lg font-bold text-brand-800">{{ $stage->name }}</h3>
                            <x-badge class="mt-1">Этап {{ $stage->order }}</x-badge>
                        </div>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-brand-100 text-brand-700">
                            @svg('lucide-flag', 'h-5 w-5')
                        </span>
                    </div>
                    <p class="mt-3 flex-1 text-sm text-slate-500">Групп: {{ $stage->groups->count() }}</p>
                    <div class="mt-5">
                        <x-btn href="{{ route('stages.show', ['tournament' => $tournament, 'stage' => $stage]) }}"
                               variant="brand" size="sm" class="w-full">
                            @svg('lucide-table', 'h-4 w-4')Таблица групп
                        </x-btn>
                    </div>
                </x-card>
            @endforeach
        </div>
    </div>
</x-app-layout>