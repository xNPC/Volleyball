<x-app-layout>
    <div>
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl font-bold tracking-tight text-brand-800">Мои игры</h1>
                <p class="mt-2 text-slate-500">Ближайшие и прошедшие игры ваших команд</p>
            </div>
            <a href="{{ route('profile.show') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-brand-700 transition hover:border-slate-300 hover:bg-slate-50">
                @svg('lucide-settings', 'h-4 w-4')Настройки
            </a>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-card class="overflow-hidden">
                <div class="flex items-center gap-2.5 border-b border-slate-100 px-6 py-4">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-accent-500/10 text-accent-500">
                        @svg('lucide-clock', 'h-4 w-4')
                    </span>
                    <h3 class="font-display text-lg font-bold text-brand-800">Ближайшие игры</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-6 py-2 font-semibold">Когда</th>
                            <th class="px-3 py-2 font-semibold">Матч</th>
                            <th class="px-3 py-2 text-right font-semibold">Площадка</th>
                            <th class="px-6 py-2 text-right font-semibold">Турнир</th>
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
                                <td class="whitespace-nowrap px-3 py-2">
                                    <span class="font-semibold text-brand-900">{{ $match['team1_name'] }}</span>
                                    <span class="px-1.5 text-slate-300">—</span>
                                    <span class="font-semibold text-brand-900">{{ $match['team2_name'] }}</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 text-right text-xs text-slate-500">
                                    <span class="inline-flex items-center gap-1">@svg('lucide-map-pin', 'inline h-3.5 w-3.5'){{ $match['location'] }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-2 text-right font-medium text-brand-700">
                                    <span class="inline-block max-w-[220px] align-middle truncate" title="{{ $match['tournament'] }}">{{ $match['tournament'] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500">Нет предстоящих игр</td>
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
                    <h3 class="font-display text-lg font-bold text-brand-800">Прошедшие игры</h3>
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
                                    <span class="font-semibold @if (($match['winner'] ?? null) === 'team1') text-emerald-600 @else text-brand-900 @endif">{{ $match['team1_name'] }}</span>
                                    <span class="px-1.5 text-slate-300">—</span>
                                    <span class="font-semibold @if (($match['winner'] ?? null) === 'team2') text-emerald-600 @else text-brand-900 @endif">{{ $match['team2_name'] }}</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 text-center">
                                    <span class="inline-block rounded-lg border border-slate-200 px-2 py-0.5 text-sm font-bold text-brand-800">{{ $match['score'] }}</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 text-xs text-slate-500">{{ $match['sets'] }}</td>
                                <td class="whitespace-nowrap px-6 py-2 text-right text-sm font-medium text-brand-700">{{ $match['tournament'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500">Нет прошедших игр</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
