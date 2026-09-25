<div>
    <x-breadcrumb :items="[['label' => 'Турниры']]" />

    <div class="mb-6 text-center">
        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-accent-500/10 text-accent-500">
            @svg('lucide-trophy', 'h-7 w-7')
        </div>
        <h1 class="font-display text-3xl font-bold tracking-tight text-brand-800 sm:text-4xl">Турниры</h1>
        <p class="mt-2 text-slate-500">Выберите турнир для просмотра деталей</p>
    </div>

    <x-card class="mb-6 p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <x-search model="search" placeholder="Поиск по названию турнира..." />
            </div>
            <div class="relative sm:w-56">
                <select wire:model.live="status"
                        class="input-base appearance-none pr-10">
                    <option value="all">Все статусы</option>
                    <option value="planned">Запланирован</option>
                    <option value="ongoing">В процессе</option>
                    <option value="completed">Завершен</option>
                </select>
                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
                    @svg('lucide-chevron-down', 'h-4 w-4')
                </span>
            </div>
        </div>
    </x-card>

    @if ($search || $status !== 'all')
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-200 bg-brand-50 px-5 py-3">
            <p class="text-sm text-brand-800">
                @svg('lucide-info', 'h-4 w-4 inline') Найдено {{ $tournaments->total() }} {{ Str::plural('турнир', $tournaments->total()) }}
            </p>
            <button type="button" wire:click="resetFilters"
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-accent-600 transition hover:text-accent-700">
                @svg('lucide-x', 'h-4 w-4')Сбросить
            </button>
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($tournaments as $tournament)
            @php
                $statusVariant = match ($tournament->status) {
                    'ongoing' => 'green',
                    'planned' => 'amber',
                    default => 'slate',
                };
                $statusLabel = \App\Models\Tournament::STATUS[$tournament->status] ?? $tournament->status;
            @endphp
            <x-card hoverable class="flex h-full flex-col p-4">
                <div class="flex items-start justify-between gap-3">
                    <x-badge :variant="$statusVariant">{{ $statusLabel }}</x-badge>
                    <x-team-logo :name="$tournament->name" size="sm" class="shrink-0" />
                </div>

                <h2 class="mt-3 break-words font-display text-base font-bold leading-snug text-brand-800">
                    {{ $tournament->name }}
                </h2>

                <div class="mt-2">
                    <x-badge variant="blue">Этапы: {{ $tournament->stages_count }}</x-badge>
                </div>

                @if ($tournament->description)
                    <p class="mt-2 flex-1 text-sm text-slate-500 line-clamp-2">{{ $tournament->description }}</p>
                @else
                    <div class="flex-1"></div>
                @endif

                <p class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 border-t border-slate-100 pt-3 text-sm text-slate-500">
                    <span class="inline-flex items-center gap-1.5">
                        @svg('lucide-calendar-days', 'h-4 w-4'){{ $tournament->start_date->format('d.m.Y') }} — @if ($tournament->end_date){{ $tournament->end_date->format('d.m.Y') }}@else TBD @endif
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        @svg('lucide-users', 'h-4 w-4'){{ $tournament->teams_count }} команд
                    </span>
                </p>

                <div class="mt-4 flex gap-2">
                    <x-btn href="{{ route('tournaments.show', $tournament) }}" variant="brand" size="sm" class="flex-1">
                        @svg('lucide-eye', 'h-4 w-4')Смотреть турнир
                    </x-btn>
                    <a href="{{ route('tournaments.teams', $tournament) }}"
                       title="Команды участники"
                       class="inline-flex w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-brand-700 transition hover:border-slate-300 hover:bg-slate-50 hover:text-accent-600">
                        @svg('lucide-users', 'h-4 w-4')
                    </a>
                </div>
            </x-card>
        @empty
            <x-card class="col-span-full py-16 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                    @svg('lucide-trophy', 'h-8 w-8')
                </div>
                <h2 class="font-display text-xl font-bold text-brand-800">Турниры не найдены</h2>
                <p class="mt-1 text-slate-500">
                    @if ($search || $status !== 'all')
                        Попробуйте изменить параметры поиска или сбросить фильтры
                    @else
                        Здесь появятся турниры, когда они будут созданы
                    @endif
                </p>
                @if ($search || $status !== 'all')
                    <div class="mt-5">
                        <x-btn wire:click="resetFilters" variant="outline-accent" size="sm">
                            @svg('lucide-rotate-ccw', 'h-4 w-4')Показать все турниры
                        </x-btn>
                    </div>
                @endif
            </x-card>
        @endforelse
    </div>

    @if ($tournaments->hasPages())
        <div class="mt-6 flex justify-center">
            {{ $tournaments->links() }}
        </div>
    @endif
</div>