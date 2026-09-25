<x-app-layout>
    <div>
        <section class="relative overflow-hidden bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 50%, rgba(255,107,53,0.8) 0, transparent 45%), radial-gradient(circle at 80% 20%, rgba(255,107,53,0.5) 0, transparent 40%);"></div>
            <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
                <div class="grid items-center gap-10 lg:grid-cols-2">
                    <div>
                        <x-badge variant="accent" class="mb-5">
                            @svg('lucide-volleyball', 'h-3.5 w-3.5')Сезон {{ date('Y') }}
                        </x-badge>
                        <h1 class="font-display text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl">
                            Волейбольные <span class="text-accent-400">Турниры</span>
                        </h1>
                        <p class="mt-4 max-w-xl text-lg leading-relaxed text-brand-200">
                            Чемпионат города Кемерово по волейболу среди мужских команд
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ route('tournaments.index') }}"
                               class="inline-flex items-center gap-2 rounded-lg bg-accent-500 px-6 py-3 font-semibold text-white shadow-card transition hover:bg-accent-600">
                                @svg('lucide-trophy', 'h-5 w-5')Смотреть турниры
                            </a>
                            <a href="{{ route('teams.index') }}"
                               class="inline-flex items-center gap-2 rounded-lg border border-white/30 bg-white/10 px-6 py-3 font-semibold text-white backdrop-blur transition hover:bg-white/20">
                                @svg('lucide-users', 'h-5 w-5')Все команды
                            </a>
                        </div>
                    </div>
                    <div class="hidden justify-center lg:flex">
                        <div class="flex h-56 w-56 items-center justify-center rounded-full border border-white/20 bg-white/10 text-accent-400 backdrop-blur">
                            @svg('lucide-volleyball', 'h-28 w-28')
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if ($featuredTournaments->count() > 0)
            <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                <div class="mb-8 text-center">
                    <h2 class="font-display text-3xl font-bold tracking-tight text-brand-800">
                        Ближайшие <span class="text-accent-500">турниры</span>
                    </h2>
                    <p class="mt-2 text-slate-500">Заявки открыты — присоединяйтесь к предстоящим соревнованиям</p>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach ($featuredTournaments as $index => $item)
                        <a href="{{ $item['url'] }}" class="group block">
                            <x-card hoverable class="flex h-full flex-col p-4">
                                <div class="flex items-center justify-between">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-accent-500/10 text-accent-500">
                                        @svg('lucide-trophy', 'h-4 w-4')
                                    </span>
                                    <span class="text-sm font-semibold text-slate-400">#{{ $index + 1 }}</span>
                                </div>
                                <h3 class="mt-3 break-words font-display text-base font-bold text-brand-800 group-hover:text-accent-600">
                                    {{ $item['name'] }}
                                </h3>
                                <p class="mt-2 flex flex-1 flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-500">
                                    <span class="inline-flex items-center gap-1.5">
                                        @svg('lucide-calendar-days', 'h-4 w-4'){{ $item['date'] }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5">
                                        @svg('lucide-map-pin', 'h-4 w-4'){{ $item['location'] }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5">
                                        @svg('lucide-users', 'h-4 w-4'){{ $item['teams_count'] }} команд
                                    </span>
                                </p>
                                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                                    <x-badge variant="blue">Приз: {{ $item['prize'] }}</x-badge>
                                    <span class="font-semibold text-accent-500 transition group-hover:translate-x-1">
                                        @svg('lucide-arrow-right', 'h-5 w-5')
                                    </span>
                                </div>
                            </x-card>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="bg-slate-100 py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 text-center">
                    <h2 class="font-display text-3xl font-bold tracking-tight text-brand-800">
                        Матчи
                    </h2>
                    <p class="mt-2 text-slate-500">Следите за результатами и предстоящими играми</p>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <x-card class="overflow-hidden">
                        <div class="flex items-center gap-2.5 border-b border-slate-100 px-6 py-4">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-accent-500/10 text-accent-500">
                                @svg('lucide-clock', 'h-4 w-4')
                            </span>
                            <h3 class="font-display text-lg font-bold text-brand-800">Ближайшие матчи</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                                    <th class="px-6 py-2 font-semibold">Когда</th>
                                    <th class="px-3 py-2 text-center font-semibold">Матч</th>
                                    <th class="px-3 py-2 font-semibold">Турнир</th>
                                    <th class="px-6 py-2 text-right font-semibold">Площадка</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                @forelse ($upcomingMatches as $match)
                                    <tr class="transition hover:bg-slate-50/70">
                                        <td class="whitespace-nowrap px-6 py-2 text-xs text-slate-500">
                                            <span class="inline-flex items-center gap-1">
                                                @svg('lucide-clock', 'inline h-3.5 w-3.5'){{ $match['time'] }}
                                            </span>
                                            <span class="mx-1.5 text-slate-300">·</span>
                                            <span class="inline-flex items-center gap-1">
                                                @svg('lucide-calendar-days', 'inline h-3.5 w-3.5'){{ $match['date'] }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex items-center justify-center gap-2 whitespace-nowrap">
                                                <span class="font-semibold text-brand-900">{{ $match['team1_name'] }}</span>
                                                <span class="text-xs font-bold text-slate-400">VS</span>
                                                <span class="font-semibold text-brand-900">{{ $match['team2_name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 font-medium text-brand-700">{{ $match['tournament'] }}</td>
                                        <td class="whitespace-nowrap px-6 py-2 text-right text-xs text-slate-500">
                                            <span class="inline-flex items-center gap-1">@svg('lucide-map-pin', 'inline h-3.5 w-3.5'){{ $match['location'] ?? 'Не указано' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-slate-500">Нет предстоящих матчей</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </x-card>

                    <x-card class="overflow-hidden">
                        <div class="flex items-center gap-2.5 border-b border-slate-100 px-6 py-4">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600/10 text-emerald-600">
                                @svg('lucide-activity', 'h-4 w-4')
                            </span>
                            <h3 class="font-display text-lg font-bold text-brand-800">Прошедшие матчи</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                                    <th class="px-6 py-2 font-semibold">Когда</th>
                                    <th class="px-3 py-2 font-semibold">Матч</th>
                                    <th class="px-3 py-2 text-center font-semibold">Счёт</th>
                                    <th class="px-3 py-2 font-semibold">Партии</th>
                                    <th class="px-6 py-2 text-right font-semibold">Турнир</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                @forelse ($pastMatches as $match)
                                    <tr class="transition hover:bg-slate-50/70">
                                        <td class="whitespace-nowrap px-6 py-2 text-xs text-slate-500">
                                            <span class="inline-flex items-center gap-1">
                                                @svg('lucide-clock', 'inline h-3.5 w-3.5'){{ $match['time'] }}
                                            </span>
                                            <span class="mx-1.5 text-slate-300">·</span>
                                            <span class="inline-flex items-center gap-1">
                                                @svg('lucide-calendar-days', 'inline h-3.5 w-3.5'){{ $match['date'] }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-2">
                                            <span class="font-semibold @if ($match['winner'] === 'team1') text-emerald-600 @else text-brand-900 @endif">{{ $match['team1_name'] }}</span>
                                            <span class="px-1.5 text-slate-300">—</span>
                                            <span class="font-semibold @if ($match['winner'] === 'team2') text-emerald-600 @else text-brand-900 @endif">{{ $match['team2_name'] }}</span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-2 text-center">
                                            <span class="inline-block rounded-lg border border-slate-200 px-2 py-0.5 text-sm font-bold text-brand-800">{{ $match['score'] }}</span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-2 text-xs text-slate-500">{{ $match['sets'] }}</td>
                                        <td class="whitespace-nowrap px-6 py-2 text-right text-sm font-medium text-brand-700">{{ $match['tournament'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">Нет данных о прошедших матчах</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </x-card>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="mb-8 text-center">
                <h2 class="font-display text-3xl font-bold tracking-tight text-brand-800">
                    Лучшие <span class="text-accent-500">команды</span>
                </h2>
                <p class="mt-2 text-slate-500">Рейтинг сильнейших коллективов активных турниров</p>
            </div>

            @if ($topTeams->count() > 0)
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($topTeams as $index => $team)
                        <x-card hoverable class="p-5 text-center">
                            <div class="relative mx-auto mb-3 w-fit">
                                <x-team-logo :name="$team['name']" size="md" />
                                @if ($index < 3)
                                    <span class="absolute -top-2 left-1/2 -translate-x-1/2 rounded-full bg-accent-500 p-1.5 text-white shadow-card">
                                        @svg('lucide-crown', 'h-3.5 w-3.5')
                                    </span>
                                @endif
                            </div>
                            <h3 class="font-display text-base font-bold text-brand-800">{{ $team['name'] }}</h3>
                            <p class="mt-1 text-xs text-slate-400">Побед: {{ $team['wins'] }} из {{ $team['total_games'] }}</p>
                            <a href="{{ route('teams.show', $team['team']) }}"
                               class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-accent-500 transition hover:text-accent-600">
                                Профиль@svg('lucide-arrow-right', 'h-4 w-4')
                            </a>
                        </x-card>
                    @endforeach
                </div>
            @else
                <x-card class="py-12 text-center text-slate-500">Нет данных о командах</x-card>
            @endif
        </section>

        <section class="relative overflow-hidden bg-gradient-to-br from-brand-700 via-brand-800 to-brand-900 py-14 text-white">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 50%, rgba(255,107,53,0.8) 0, transparent 45%), radial-gradient(circle at 80% 20%, rgba(255,107,53,0.5) 0, transparent 40%);"></div>
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 text-center">
                    <h2 class="font-display text-3xl font-bold tracking-tight">
                        Дни рождения сегодня
                    </h2>
                    <p class="mt-2 text-brand-200">Поздравьте наших игроков!</p>
                </div>

                @if ($birthdayUsers->count() > 0)
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($birthdayUsers as $user)
                            <div class="rounded-xl bg-white/10 p-6 text-center backdrop-blur">
                                <div class="relative mx-auto mb-3 w-fit">
                                    <x-avatar :photo="$user->profile_photo_thumb_url ?? ($user->profile_photo_url ?? null)"
                                              :name="$user->name"
                                              size="lg" />
                                    <span class="absolute -right-1 -top-1 flex h-7 w-7 items-center justify-center rounded-full bg-accent-500 text-white shadow-card">
                                        @svg('lucide-cake-slice', 'h-3.5 w-3.5')
                                    </span>
                                </div>
                                <h3 class="font-display text-base font-bold">{{ $user->name }}</h3>
                                <p class="mt-1 text-sm text-brand-100">Исполняется {{ (int) $user->age }} лет</p>
                                <p class="mt-0.5 text-xs text-brand-200">{{ $user->position }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center">
                        <div class="mb-3 flex justify-center">
                            @svg('lucide-cake-slice', 'h-12 w-12 text-brand-300')
                        </div>
                        <p class="text-lg">Сегодня нет дней рождения</p>
                        <p class="text-brand-200">Возвращайтесь завтра!</p>
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-app-layout>