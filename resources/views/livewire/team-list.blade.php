<div>
    <x-breadcrumb :items="[['label' => 'Команды']]" />

    <div class="mb-6 text-center">
        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-accent-500/10 text-accent-500">
            @svg('lucide-shield', 'h-7 w-7')
        </div>
        <h1 class="font-display text-3xl font-bold tracking-tight text-brand-800 sm:text-4xl">Команды</h1>
        <p class="mt-2 text-slate-500">Список всех зарегистрированных команд</p>
    </div>

    <x-card class="mb-6 p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <x-search model="search" placeholder="Поиск по названию команды..." />
            </div>
            <div class="relative sm:w-64">
                <select wire:model.live="filter"
                        class="input-base appearance-none pr-10">
                    <option value="all">Все команды</option>
                    <option value="with_tournaments">Участвуют в турнирах</option>
                    <option value="new">Новые за месяц</option>
                </select>
                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
                    @svg('lucide-chevron-down', 'h-4 w-4')
                </span>
            </div>
        </div>
    </x-card>

    @if ($search || $filter !== 'all')
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-200 bg-brand-50 px-5 py-3">
            <p class="text-sm text-brand-800">
                @svg('lucide-info', 'h-4 w-4 inline') Найдено {{ $teams->total() }} {{ Str::plural('команд', $teams->total()) }}
            </p>
            <button type="button" wire:click="resetFilters"
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-accent-600 transition hover:text-accent-700">
                @svg('lucide-x', 'h-4 w-4')Сбросить
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
                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Турниров: {{ $team->active_tournaments_count }}</span>
                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Заявок: {{ $team->applications_count }}</span>
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

                <div class="mt-4 flex-1"></div>

                <x-btn href="{{ route('teams.show', $team) }}" variant="brand" size="sm" class="w-full">
                    @svg('lucide-eye', 'h-4 w-4')Профиль команды
                </x-btn>
            </x-card>
        @empty
            <x-card class="col-span-full py-16 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                    @svg('lucide-shield', 'h-8 w-8')
                </div>
                <h2 class="font-display text-xl font-bold text-brand-800">Команды не найдены</h2>
                <p class="mt-1 text-slate-500">
                    @if ($search || $filter !== 'all')
                        Попробуйте изменить параметры поиска или сбросить фильтры
                    @else
                        Здесь появятся команды, когда они будут созданы
                    @endif
                </p>
                @if ($search || $filter !== 'all')
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