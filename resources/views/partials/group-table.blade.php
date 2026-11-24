@php
    $standings = $group->standings;
    $games = $group->standings_games ?? collect();

    $getGameDetails = function($games, $team1Id, $team2Id, $team1Name, $team2Name, $score) {
        $game = $games->first(function($game) use ($team1Id, $team2Id) {
            return ($game->home_application_id == $team1Id && $game->away_application_id == $team2Id) ||
                   ($game->home_application_id == $team2Id && $game->away_application_id == $team1Id);
        });

        if (!$game || $game->sets->isEmpty()) {
            return null;
        }

        $setsDetails = [];
        foreach ($game->sets as $set) {
            $firstScore = $game->home_application_id == $team1Id ? $set->home_score : $set->away_score;
            $secondScore = $game->home_application_id == $team1Id ? $set->away_score : $set->home_score;
            $setsDetails[] = "{$firstScore}:{$secondScore}";
        }

        return [
            'key' => $team1Id . '-' . $team2Id,
            'title' => $team1Name . ' — ' . $team2Name,
            'score' => $score,
            'sets' => 'Сеты: ' . implode(', ', $setsDetails),
            'source' => $game->source_stage_name ? 'из этапа «' . $game->source_stage_name . '»' : '',
        ];
    };
@endphp

@if ($standings->isNotEmpty())
    <div x-data="{
            activeResult: null,
            openResult(event, details, toggle = true) {
                if (this.activeResult?.key === details.key) {
                    if (toggle) this.activeResult = null;
                    return;
                }
                const rect = event.currentTarget.getBoundingClientRect();
                const mobile = window.innerWidth < 640;
                let style = 'width:max-content;min-width:18rem;max-width:calc(100vw - 2rem);';
                if (mobile) {
                    style += 'left:50%;bottom:1rem;transform:translateX(-50%);';
                } else {
                    const horizontal = rect.left + rect.width / 2 <= window.innerWidth / 2
                        ? `left:${Math.max(8, rect.left)}px;`
                        : `right:${Math.max(8, window.innerWidth - rect.right)}px;`;
                    const vertical = window.innerHeight - rect.bottom > 190
                        ? `top:${rect.bottom + 8}px;`
                        : `bottom:${window.innerHeight - rect.top + 8}px;`;
                    style += horizontal + vertical;
                }
                this.activeResult = { ...details, style };
            }
         }"
         @keydown.escape.window="activeResult = null"
         @resize.window="activeResult = null"
         @click.outside="activeResult = null">
        <div class="overflow-x-auto" @scroll="activeResult = null">
        <table class="w-full border-collapse text-xs whitespace-nowrap">
            <thead>
            <tr class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <th rowspan="2" class="border-b border-slate-100 px-3 py-2 text-left font-semibold">№</th>
                <th rowspan="2" class="border-b border-slate-100 px-3 py-2 text-left font-semibold">Команда</th>
                @foreach ($standings as $index => $team)
                    <th rowspan="2" class="border-b border-slate-100 px-2 py-2 font-semibold">{{ $index + 1 }}</th>
                @endforeach
                <th colspan="3" class="border-b border-slate-100 px-3 py-2 text-center font-semibold">Игры</th>
                <th rowspan="2" class="border-b border-slate-100 bg-accent-500/10 px-3 py-2 font-semibold text-accent-700">Очки</th>
                <th colspan="3" class="border-b border-slate-100 px-3 py-2 text-center font-semibold">Партии</th>
                <th colspan="3" class="border-b border-slate-100 px-3 py-2 text-center font-semibold">Мячи</th>
            </tr>
            <tr class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <th class="border-b border-slate-100 px-2 py-1 font-semibold">Всего</th>
                <th class="border-b border-slate-100 px-2 py-1 font-semibold">В</th>
                <th class="border-b border-slate-100 px-2 py-1 font-semibold">П</th>
                <th class="border-b border-slate-100 px-2 py-1 font-semibold">В</th>
                <th class="border-b border-slate-100 px-2 py-1 font-semibold">П</th>
                <th class="border-b border-slate-100 px-2 py-1 font-semibold">Коэф</th>
                <th class="border-b border-slate-100 px-2 py-1 font-semibold">В</th>
                <th class="border-b border-slate-100 px-2 py-1 font-semibold">П</th>
                <th class="border-b border-slate-100 px-2 py-1 font-semibold">Коэф</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($standings as $index => $teamStats)
                @php
                    $rowClass = $index < 2
                        ? 'bg-emerald-50/60'
                        : ($index >= $standings->count() - 2 ? 'bg-red-50/60' : '');
                @endphp
                <tr class="{{ $rowClass }} border-b border-slate-100">
                    <td class="px-3 py-2 font-bold text-brand-800">{{ $index + 1 }}</td>
                    <td class="px-3 py-2 text-left font-semibold text-brand-900">{{ $teamStats['team_name'] }}</td>

                    @foreach ($standings as $opponentIndex => $opponentStats)
                        @if ($teamStats['team']->id === $opponentStats['team']->id)
                            <td class="border-r border-slate-100 bg-slate-100 px-2 py-2 text-center">
                                @svg('lucide-volleyball', 'mx-auto h-3.5 w-3.5 text-slate-500')
                            </td>
                        @else
                            @php
                                $result = $teamStats['results'][$opponentStats['team']->id] ?? null;
                                $score = $result ? $result['score'] : '-:-';
                                $gameDetails = $getGameDetails($games, $teamStats['team']->id, $opponentStats['team']->id, $teamStats['team_name'], $opponentStats['team_name'], $score);
                                $scoreClass = $result
                                    ? ($result['class'] === 'win-score' ? 'font-bold text-emerald-600' : 'font-bold text-red-600')
                                    : 'text-slate-400';
                            @endphp
                            <td class="border-r border-slate-100 px-2 py-2 text-center transition hover:bg-slate-50">
                                @if ($gameDetails)
                                    <button type="button"
                                            @click="openResult($event, @js($gameDetails))"
                                            @mouseenter="if (window.matchMedia('(hover: hover)').matches) openResult($event, @js($gameDetails), false)"
                                            class="block w-full rounded px-1 py-0.5 text-center {{ $scoreClass }}">
                                        {{ $score }}
                                    </button>
                                @else
                                    <span class="{{ $scoreClass }}">{{ $score }}</span>
                                @endif
                            </td>
                        @endif
                    @endforeach

                    <td class="border-r border-slate-100 px-2 py-2 text-center font-bold text-brand-900">{{ $teamStats['games_played'] }}</td>
                    <td class="border-r border-slate-100 px-2 py-2 text-center font-bold text-emerald-600">{{ $teamStats['games_won'] }}</td>
                    <td class="border-r border-slate-100 px-2 py-2 text-center font-bold text-red-600">{{ $teamStats['games_lost'] }}</td>
                    <td class="border-r border-slate-100 bg-brand-50 px-2 py-2 text-center font-bold text-brand-800">{{ $teamStats['points'] }}</td>
                    <td class="border-r border-slate-100 px-2 py-2 text-center font-semibold text-slate-700">{{ $teamStats['sets_won'] }}</td>
                    <td class="border-r border-slate-100 px-2 py-2 text-center font-semibold text-slate-700">{{ $teamStats['sets_lost'] }}</td>
                    <td class="border-r border-slate-100 px-2 py-2 text-center font-semibold text-slate-700">{{ $teamStats['sets_ratio'] }}</td>
                    <td class="border-r border-slate-100 px-2 py-2 text-center font-semibold text-slate-700">{{ $teamStats['points_won'] }}</td>
                    <td class="border-r border-slate-100 px-2 py-2 text-center font-semibold text-slate-700">{{ $teamStats['points_lost'] }}</td>
                    <td class="px-2 py-2 text-center font-semibold text-slate-700">{{ $teamStats['points_ratio'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>

        <div x-cloak x-show="activeResult" x-transition.opacity.duration.100ms
             class="fixed z-50"
             :style="activeResult?.style">
            <div class="relative rounded-xl border border-slate-200 bg-white/95 p-4 shadow-xl backdrop-blur-sm">
                <button type="button" @click="activeResult = null" aria-label="Закрыть"
                        style="right:0.5rem;top:0.5rem;"
                        class="absolute rounded-md p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                    @svg('lucide-x', 'h-4 w-4')
                </button>

                <p class="mt-5 break-words text-center text-sm font-semibold leading-snug text-brand-900"
                   x-text="activeResult?.title"></p>

                <p class="mt-3 text-center text-xl font-bold tracking-tight text-brand-800"
                   x-text="activeResult?.score"></p>

                <div class="mt-3 rounded-lg bg-slate-50 px-3 py-2.5">
                    <p class="text-center text-xs leading-relaxed text-slate-600" x-text="activeResult?.sets"></p>
                </div>

                <p x-show="activeResult?.source" class="mt-2 text-center text-[11px] font-medium text-accent-700"
                   x-text="activeResult?.source"></p>
            </div>
        </div>
    </div>
@else
    <div class="p-8 text-center text-slate-500">
        @svg('lucide-info', 'mx-auto mb-2 h-6 w-6 text-slate-400')
        В этой группе пока нет статистики.
    </div>
@endif