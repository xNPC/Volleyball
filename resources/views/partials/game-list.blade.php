@php
    if (! $games instanceof \Illuminate\Support\Collection) {
        $games = collect($games);
    }
    $games->load('homeApplication.team', 'awayApplication.team', 'sets');
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
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @foreach ($games as $game)
                @php
                    $played = $game->home_score !== null && $game->away_score !== null;
                    $homeName = $game->homeApplication->team->name ?? 'Не определено';
                    $awayName = $game->awayApplication->team->name ?? 'Не определено';
                @endphp
                <tr class="transition hover:bg-slate-50/70">
                    <td class="whitespace-nowrap px-4 py-2 text-xs text-slate-500">
                        {{ $game->scheduled_time ? $game->scheduled_time->format('d.m.Y H:i') : '—' }}
                    </td>
                    <td class="px-3 py-2">
                        <span class="flex items-center gap-2 whitespace-nowrap font-medium text-brand-900">
                            <x-team-logo :name="$homeName" size="sm" />
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
                            <x-team-logo :name="$awayName" size="sm" />
                        </span>
                    </td>
                    <td class="px-3 py-2 text-xs text-slate-500">
                        {{ $played && $game->sets->count() > 0 ? $game->sets->map(fn($set) => $set->home_score . ':' . $set->away_score)->implode(', ') : '—' }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-2 text-right text-xs text-slate-500">
                        {{ $game->venue ? $game->venue->name : '—' }}
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