<div>
    <x-breadcrumb :items="[['label' => 'Игроки']]" />

    <div class="mb-6 text-center">
        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-accent-500/10 text-accent-500">
            @svg('lucide-users', 'h-7 w-7')
        </div>
        <h1 class="font-display text-3xl font-bold tracking-tight text-brand-800 sm:text-4xl">Игроки</h1>
        <p class="mt-2 text-slate-500">Список всех зарегистрированных игроков</p>
    </div>

    <x-card class="mb-6 p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <x-search model="search" placeholder="Поиск по имени или email..." />
            </div>
            <div class="relative sm:w-60">
                <select wire:model.live="filter"
                        class="input-base appearance-none pr-10">
                    <option value="all">Все игроки</option>
                    <option value="with_teams">С командами</option>
                    <option value="new">Новые за месяц</option>
                    <option value="verified">Подтвержденные</option>
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
                @svg('lucide-info', 'h-4 w-4 inline') Найдено {{ $users->total() }} {{ Str::plural('игрок', $users->total()) }}
            </p>
            <button type="button" wire:click="resetFilters"
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-accent-600 transition hover:text-accent-700">
                @svg('lucide-x', 'h-4 w-4')Сбросить
            </button>
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse ($users as $user)
            <x-card hoverable class="flex h-full flex-col items-center px-4 py-5 text-center">
                @if ($user->profile_photo_path)
                    <div data-photo="{{ asset('storage/' . $user->profile_photo_path) }}"
                         data-name="{{ $user->name }}"
                         data-profile-url="{{ route('users.show', $user) }}"
                         class="cursor-pointer"
                         title="Посмотреть фото">
                        <x-avatar :photo="$user->profile_photo_thumb_url" :name="$user->name" size="lg" />
                    </div>
                @else
                    <x-avatar :name="$user->name" size="lg" />
                @endif

                <h2 class="mt-3 font-display text-base font-bold text-brand-800">{{ $user->name }}</h2>

                <p class="mt-0.5 text-xs text-slate-400">Участник с {{ $user->created_at->format('d.m.Y') }}</p>

                <p class="mt-1 text-sm">
                    @if ($user->email_verified_at)
                        <span class="inline-flex items-center gap-1 text-emerald-600">
                            @svg('lucide-badge-check', 'h-4 w-4')Подтвержден
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-amber-600">
                            @svg('lucide-clock', 'h-4 w-4')Не подтвержден
                        </span>
                    @endif
                </p>

                <div class="mt-3 flex items-center gap-2">
                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Команд: {{ $user->approved_applications_count }}</span>
                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Турниров: {{ $user->total_applications_count }}</span>
                </div>

                <div class="mt-4 w-full">
                    <x-btn href="{{ route('users.show', $user) }}" variant="brand" size="sm" class="w-full">
                        @svg('lucide-eye', 'h-4 w-4')Профиль
                    </x-btn>
                </div>
            </x-card>
        @empty
            <x-card class="col-span-full py-16 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                    @svg('lucide-users', 'h-8 w-8')
                </div>
                <h2 class="font-display text-xl font-bold text-brand-800">Игроки не найдены</h2>
                <p class="mt-1 text-slate-500">
                    @if ($search || $filter !== 'all')
                        Попробуйте изменить параметры поиска или сбросить фильтры
                    @else
                        Здесь появятся игроки, когда они зарегистрируются
                    @endif
                </p>
                @if ($search || $filter !== 'all')
                    <div class="mt-5">
                        <x-btn wire:click="resetFilters" variant="outline-accent" size="sm">
                            @svg('lucide-rotate-ccw', 'h-4 w-4')Показать всех игроков
                        </x-btn>
                    </div>
                @endif
            </x-card>
        @endforelse
    </div>

    @if ($users->hasPages())
        <div class="mt-6 flex justify-center">
            {{ $users->links() }}
        </div>
    @endif
</div>