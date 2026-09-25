<div>
    <x-breadcrumb :items="[
        ['label' => 'Турниры', 'url' => route('tournaments.index')],
        ['label' => $tournament->name, 'url' => route('tournaments.show', $tournament)],
        ['label' => 'Команды'],
    ]" />

    <x-card class="mb-6 p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    @svg('lucide-trophy', 'h-4 w-4 text-accent-500')
                    <span>{{ $tournament->volleyball_type }}</span>
                </div>
                <h1 class="mt-1 font-display text-2xl font-bold tracking-tight text-brand-800 sm:text-3xl">
                    {{ $tournament->name }}
                </h1>
                <p class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-500">
                    <span class="inline-flex items-center gap-1.5">
                        @svg('lucide-calendar-days', 'h-4 w-4'){{ $tournament->start_date->format('d.m.Y') }} — @if ($tournament->end_date){{ $tournament->end_date->format('d.m.Y') }}@else TBD @endif
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        @svg('lucide-users', 'h-4 w-4')Команд: {{ $stats['total_teams'] }}
                    </span>
                    @if ($stats['pending_applications'] > 0)
                        <span class="inline-flex items-center gap-1.5 text-amber-600">
                            @svg('lucide-clock', 'h-4 w-4'){{ $stats['pending_applications'] }} заявок на рассмотрении
                        </span>
                    @endif
                </p>
            </div>
            <div class="shrink-0">
                <x-badge variant="green">
                    @svg('lucide-check-circle', 'h-3.5 w-3.5')Активен
                </x-badge>
            </div>
        </div>
    </x-card>

    <x-card class="mb-6 p-5">
        <x-search model="search" placeholder="Поиск по названию команды или описанию..." />
    </x-card>

    @if ($search)
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-200 bg-brand-50 px-5 py-3">
            <p class="text-sm text-brand-800">
                @svg('lucide-info', 'h-4 w-4 inline') Найдено {{ $teams->total() }} {{ Str::plural('команд', $teams->total()) }}
            </p>
            <button type="button" wire:click="resetFilters"
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-accent-600 transition hover:text-accent-700">
                @svg('lucide-x', 'h-4 w-4')Сбросить поиск
            </button>
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($teams as $team)
            <x-card hoverable class="flex h-full flex-col p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="break-words font-display text-base font-bold text-brand-800">{{ $team->name }}</h2>
                        @if ($team->city)
                            <p class="mt-0.5 text-xs text-slate-500">
                                @svg('lucide-map-pin', 'inline h-3.5 w-3.5'){{ $team->city }}
                            </p>
                        @endif
                    </div>
                    <x-team-logo :name="$team->name" size="sm" class="shrink-0" />
                </div>

                @if ($team->description)
                    <p class="mt-2 text-sm text-slate-500 line-clamp-2">{{ $team->description }}</p>
                @endif

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Турниров: {{ $team->tournaments_count ?? 0 }}</span>
                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Заявок: {{ $team->applications_count ?? 0 }}</span>
                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Игроков: ?</span>
                </div>

                @if ($team->captain)
                    <div class="mt-3 flex items-center gap-2">
                        <x-avatar :photo="$team->captain->profile_photo_path ? $team->captain->profile_photo_thumb_url : null"
                                  :name="$team->captain->name"
                                  size="xs" />
                        <div class="min-w-0">
                            <div class="text-[11px] uppercase tracking-wide text-slate-400">Капитан</div>
                            <div class="truncate text-sm font-medium text-brand-800">{{ $team->captain->name }}</div>
                        </div>
                    </div>
                @endif

                <p class="mt-3 border-t border-slate-100 pt-3 text-xs text-slate-400">
                    @svg('lucide-calendar-check', 'inline h-3.5 w-3.5') Участник с: {{ $team->pivot->created_at->format('d.m.Y') }}
                </p>

                <div class="mt-4 flex-1"></div>

                <div class="flex flex-col gap-2">
                    <x-btn href="{{ route('teams.show', $team) }}" variant="brand" size="sm" class="w-full">
                        @svg('lucide-eye', 'h-4 w-4')Профиль команды
                    </x-btn>
                    <x-btn href="{{ route('tournaments.teams.roster', ['tournament' => $tournament, 'team' => $team]) }}"
                           variant="outline" size="sm" class="w-full">
                        @svg('lucide-users', 'h-4 w-4')Состав в турнире
                    </x-btn>
                </div>
            </x-card>
        @empty
            <x-card class="col-span-full py-16 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                    @svg('lucide-users', 'h-8 w-8')
                </div>
                <h2 class="font-display text-xl font-bold text-brand-800">
                    @if ($search)
                        Команды не найдены
                    @else
                        В турнире пока нет команд
                    @endif
                </h2>
                <p class="mt-1 text-slate-500">
                    @if ($search)
                        Попробуйте изменить поисковый запрос
                    @else
                        Команды появятся здесь после одобрения их заявок
                    @endif
                </p>
                @if ($search)
                    <div class="mt-5">
                        <x-btn wire:click="resetFilters" variant="outline-accent" size="sm">
                            @svg('lucide-rotate-ccw', 'h-4 w-4')Показать все команды
                        </x-btn>
                    </div>
                @endif
            </x-card>
        @endforelse
    </div>

    @if ($teams->hasPages())
        <div class="mt-6 flex justify-center">
            {{ $teams->links() }}
        </div>
    @endif
</div>