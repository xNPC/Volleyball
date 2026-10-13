@php
    if (! $games instanceof \Illuminate\Support\Collection) {
        $games = collect($games);
    }
    $games->load('homeApplication.team', 'awayApplication.team', 'sets', 'broadcasts');
@endphp

@if ($games->count() > 0)
    <div class="overflow-x-auto rounded-xl border border-slate-200/70 bg-white shadow-card">
        <table class="w-full text-sm">
            <thead>
            <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <th class="px-4 py-2 font-semibold">Дата / Время</th>
                <th class="px-3 py-2 font-semibold">Команда 1</th>
                <th class="px-3 py-2 text-center font-semibold">Счёт</th>
                <th class="px-3 py-2 font-semibold">Команда 2</th>
                <th class="px-3 py-2 font-semibold">Сеты</th>
                <th class="px-4 py-2 text-right font-semibold">Площадка</th>
                <th class="px-4 py-2 text-right font-semibold">Эфир</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @foreach ($games as $game)
                @php
                    $played = $game->home_score !== null && $game->away_score !== null;
                    $homeTeam = $game->homeApplication->team ?? null;
                    $awayTeam = $game->awayApplication->team ?? null;
                    $homeName = $homeTeam->name ?? 'Не определено';
                    $awayName = $awayTeam->name ?? 'Не определено';
                @endphp
                <tr class="transition hover:bg-slate-50/70">
                    <td class="whitespace-nowrap px-4 py-2 text-xs text-slate-500">
                        {{ $game->scheduled_time ? $game->scheduled_time->format('d.m.Y H:i') : '—' }}
                    </td>
                    <td class="px-3 py-2">
                        <span class="flex items-center gap-2 whitespace-nowrap font-medium text-brand-900">
                            <x-team-logo :name="$homeName" :logo="$homeTeam?->logo_url" size="sm" />
                            {{ $homeName }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-3 py-2 text-center">
                        @if ($played)
                            <span class="inline-block rounded-lg border border-slate-200 px-2 py-0.5 font-bold text-brand-800">
                                {{ $game->home_score }}:{{ $game->away_score }}
                            </span>
                        @else
                            <span class="text-slate-400">vs</span>
                        @endif
                    </td>
                    <td class="px-3 py-2">
                        <span class="flex items-center gap-2 whitespace-nowrap font-medium text-brand-900">
                            {{ $awayName }}
                            <x-team-logo :name="$awayName" :logo="$awayTeam?->logo_url" size="sm" />
                        </span>
                    </td>
                    <td class="px-3 py-2 text-xs text-slate-500">
                        {{ $played && $game->sets->count() > 0 ? $game->sets->map(fn($set) => $set->home_score . ':' . $set->away_score)->implode(', ') : '—' }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-2 text-right text-xs text-slate-500">
                        {{ $game->venue ? $game->venue->name : '—' }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-2 text-right">
                        @php
                            $liveBroadcast = $game->broadcasts->firstWhere('status', 'live');
                            $upcomingBroadcast = $game->broadcasts->firstWhere('status', 'scheduled');
                            $broadcast = $liveBroadcast ?? $upcomingBroadcast;
                        @endphp

                        @if ($broadcast)
                            @if ($liveBroadcast)
                                <a href="{{ route('broadcasts.show', $liveBroadcast) }}"
                                   class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700 transition hover:bg-red-200">
                                    <span class="relative flex h-1.5 w-1.5">
                                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-500 opacity-75"></span>
                                        <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-red-600"></span>
                                    </span>
                                    Смотреть
                                </a>
                            @else
                                <a href="{{ route('broadcasts.show', $upcomingBroadcast) }}"
                                   class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800 transition hover:bg-amber-200">
                                    Скоро
                                </a>
                            @endif
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@else
    <x-card class="p-8 text-center text-slate-500">
        @svg('lucide-info', 'mx-auto mb-2 h-6 w-6 text-slate-400')
        В этой группе пока нет запланированных игр.
    </x-card>
@endif