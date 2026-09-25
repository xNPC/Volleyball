@php
    $standings = $group->standings;
    $games = $group->games()->with('sets', 'homeApplication.team', 'awayApplication.team')->get();

    $getGameDetails = function($games, $team1Id, $team2Id) {
        $game = $games->first(function($game) use ($team1Id, $team2Id) {
            return ($game->home_application_id == $team1Id && $game->away_application_id == $team2Id) ||
                   ($game->home_application_id == $team2Id && $game->away_application_id == $team1Id);
        });

        if (!$game || $game->sets->isEmpty()) {
            return null;
        }

        $setsDetails = [];
        foreach ($game->sets as $set) {
            $setsDetails[] = "{$set->home_score}:{$set->away_score}";
        }

        return "Сеты: " . implode(', ', $setsDetails);
    };
@endphp

@if ($standings->isNotEmpty())
    <div class="overflow-x-auto">
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
                                $gameDetails = $getGameDetails($games, $teamStats['team']->id, $opponentStats['team']->id);
                                $scoreClass = $result
                                    ? ($result['class'] === 'win-score' ? 'font-bold text-emerald-600' : 'font-bold text-red-600')
                                    : 'text-slate-400';
                            @endphp
                            <td class="border-r border-slate-100 px-2 py-2 text-center transition hover:bg-slate-50 {{ $scoreClass }}"
                                {!! $gameDetails ? 'title="' . $gameDetails . '"' : '' !!}>
                                {{ $result ? $result['score'] : '-:-' }}
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
@else
    <div class="p-8 text-center text-slate-500">
        @svg('lucide-info', 'mx-auto mb-2 h-6 w-6 text-slate-400')
        В этой группе пока нет статистики.
    </div>
@endif