@section('title', 'Игры')
@section('description', 'Список всех игр по турнирам: предстоящие и прошедшие матчи, счёт и партии.')
@section('og_type', 'website')
@section('og_title', 'Игры')
@section('canonical', request()->url())
@php $canonicalUrl = request()->url(); @endphp

<div>
    <x-breadcrumb :items="[['label' => 'Игры']]" />

    <div class="mb-6 text-center">
        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-accent-500/10 text-accent-500">
            @svg('lucide-calendar-days', 'h-7 w-7')
        </div>
        <h1 class="font-display text-3xl font-bold tracking-tight text-brand-800 sm:text-4xl">Игры</h1>
        <p class="mt-2 text-slate-500">Все матчи турниров: предстоящие и прошедшие</p>
    </div>

    <x-card class="mb-6 p-5">
        <div class="grid min-w-0 gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div class="min-w-0 xl:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Команда</label>
                <x-search model="search" placeholder="Поиск по названию команды..." />
            </div>

            <div class="min-w-0">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Период</label>
                <select wire:model.live="period" class="input-base">
                    <option value="all">Все игры</option>
                    <option value="upcoming">Предстоящие</option>
                    <option value="past">Прошедшие</option>
                </select>
            </div>

            <div class="min-w-0">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Статус</label>
                <select wire:model.live="status" class="input-base">
                    <option value="all">Все статусы</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-0">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Дата с</label>
                <input type="date" wire:model.live="dateFrom" class="input-base">
            </div>

            <div class="min-w-0">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Дата по</label>
                <input type="date" wire:model.live="dateTo" class="input-base">
            </div>

            <div class="min-w-0">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Сортировка</label>
                <select wire:model.live="sort" class="input-base">
                    <option value="auto">По умолчанию</option>
                    <option value="date_desc">Сначала новые</option>
                    <option value="date_asc">Сначала старые</option>
                </select>
            </div>

            <div class="min-w-0" x-data="{ open: false, tournamentSearch: '' }">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Турниры</label>
                <div class="relative">
                    <button type="button" @click="open = !open; if (open) $nextTick(() => $refs.tournamentSearch?.focus())" @click.outside="open = false"
                            class="input-base flex w-full items-center justify-between gap-2 text-left">
                        <span class="truncate text-slate-700">
                            @if (count($tournaments) > 0)
                                Выбрано турниров: {{ count($tournaments) }}
                            @else
                                Все турниры
                            @endif
                        </span>
                        @svg('lucide-chevron-down', 'h-4 w-4 shrink-0 text-slate-400')
                    </button>

                    <div x-cloak x-show="open" x-transition
                         class="absolute right-0 z-20 mt-1 w-[min(32rem,calc(100vw-2rem))] rounded-lg border border-slate-200 bg-white p-2 shadow-lg">
                        <div class="mb-2 px-1">
                            <input type="text" x-ref="tournamentSearch" x-model.debounce.150ms="tournamentSearch"
                                   placeholder="Поиск турнира..."
                                   class="input-base py-2">
                        </div>
                        <div class="max-h-64 overflow-auto" x-ref="tournamentOptions">
                            @forelse ($tournamentsList as $tournament)
                                <label data-tournament-option title="{{ $tournament->name }}"
                                       x-show="!tournamentSearch || @js(mb_strtolower($tournament->name)).includes(tournamentSearch.toLowerCase())"
                                       class="flex cursor-pointer items-start gap-2 rounded-md px-2 py-1.5 text-sm text-slate-700 hover:bg-slate-50">
                                    <input type="checkbox" value="{{ $tournament->id }}" wire:model.live="tournaments"
                                           class="mt-0.5 shrink-0 rounded border-slate-300 text-accent-500 focus:ring-accent-500/20">
                                    <span class="min-w-0 whitespace-normal break-words leading-snug">{{ $tournament->name }}</span>
                                </label>
                            @empty
                                <div class="px-2 py-1.5 text-sm text-slate-500">Нет турниров с играми</div>
                            @endforelse
                            <div x-cloak x-show="tournamentSearch && !@js($tournamentsList->map(fn ($t) => mb_strtolower($t->name))->values()).some(n => n.includes(tournamentSearch.toLowerCase()))" class="px-2 py-1.5 text-sm text-slate-500">Ничего не найдено</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
            <div class="text-sm text-slate-500">
                Найдено игр: <span class="font-semibold text-brand-700">{{ $games->total() }}</span>
            </div>
            <button type="button" wire:click="resetFilters"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-brand-700">
                @svg('lucide-x', 'h-4 w-4')Сбросить фильтры
            </button>
        </div>
    </x-card>

    <x-card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-6 py-3 font-semibold">Когда</th>
                    <th class="px-3 py-3 font-semibold">Матч</th>
                    <th class="px-3 py-3 text-center font-semibold">Счёт</th>
                    <th class="px-3 py-3 font-semibold">Сеты</th>
                    <th class="px-3 py-3 font-semibold">Статус</th>
                    <th class="px-6 py-3 text-right font-semibold">Турнир</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($games as $game)
                    @php
                        $homeTeam = $game->homeApplication?->team?->name ?? 'TBA';
                        $awayTeam = $game->awayApplication?->team?->name ?? 'TBA';
                        $tournament = $game->stage?->tournament;
                        $hasScore = !is_null($game->home_score) && !is_null($game->away_score);
                        $winner = $hasScore ? ($game->home_score > $game->away_score ? 'home' : ($game->away_score > $game->home_score ? 'away' : null)) : null;
                        $sets = $game->sets->sortBy('set_number')->map(fn ($set) => $set->home_score . ':' . $set->away_score)->implode(', ');
                        $statusClasses = match ($game->status) {
                            'live' => 'bg-red-100 text-red-700',
                            'completed' => 'bg-emerald-100 text-emerald-700',
                            'cancelled' => 'bg-slate-100 text-slate-600',
                            default => 'bg-blue-100 text-blue-700',
                        };
                    @endphp
                    <tr class="transition hover:bg-slate-50/70">
                        <td class="whitespace-nowrap px-6 py-3 text-xs text-slate-500">
                            @if ($game->scheduled_time)
                                <div class="inline-flex items-center gap-1">
                                    @svg('lucide-clock', 'inline h-3.5 w-3.5'){{ $game->scheduled_time->format('H:i') }}
                                </div>
                                <span class="mx-1.5 text-slate-300">·</span>
                                <div class="inline-flex items-center gap-1">
                                    @svg('lucide-calendar-days', 'inline h-3.5 w-3.5'){{ $game->scheduled_time->format('d.m.Y') }}
                                </div>
                            @else
                                <span class="text-slate-400">Время не указано</span>
                            @endif
                            @if ($game->venue)
                                <div class="mt-1 inline-flex items-center gap-1 text-slate-400">
                                    @svg('lucide-map-pin', 'inline h-3.5 w-3.5'){{ $game->venue->name }}
                                </div>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-3">
                            <span class="font-semibold @if ($winner === 'home') text-emerald-600 @else text-brand-900 @endif">{{ $homeTeam }}</span>
                            <span class="px-1.5 text-slate-300">—</span>
                            <span class="font-semibold @if ($winner === 'away') text-emerald-600 @else text-brand-900 @endif">{{ $awayTeam }}</span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-3 text-center">
                            @if ($hasScore)
                                <span class="inline-block rounded-lg border border-slate-200 px-2 py-0.5 text-sm font-bold text-brand-800">{{ $game->home_score }}:{{ $game->away_score }}</span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-3 text-xs text-slate-500">
                            {{ $sets !== '' ? $sets : '—' }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClasses }}">
                                {{ $statuses[$game->status] ?? $game->status }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right font-medium text-brand-700">
                            @if ($tournament)
                                <a href="{{ route('tournaments.show', $tournament) }}" class="inline-block max-w-[320px] align-middle truncate transition hover:text-accent-600" title="{{ $tournament->name }}">
                                    {{ $tournament->name }}
                                </a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-500">Игры не найдены</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    @if ($games->hasPages())
        <div class="mt-6">
            {{ $games->links() }}
        </div>
    @endif
</div>
