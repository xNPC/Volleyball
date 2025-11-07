@php
    $standings = $group->standings;
    $games = $group->games()->with('sets', 'homeApplication.team', 'awayApplication.team')->get();

    // Функция для получения деталей игры
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

<div class="table-responsive tournament-table">
    <table class="table table-bordered table-hover mb-0">
        <thead>
        <tr>
            <th rowspan="2">№</th>
            <th rowspan="2">Команда</th>
            @foreach($standings as $index => $team)
                <th rowspan="2">{{ $index + 1 }}</th>
            @endforeach
            <th colspan="3">Игры</th>
            <th rowspan="2">Очки</th>
            <th colspan="3">Партии</th>
            <th colspan="3">Мячи</th>
        </tr>
        <tr>
            <th>Всего</th>
            <th>В</th>
            <th>П</th>
            <th>В</th>
            <th>П</th>
            <th>Коэф</th>
            <th>В</th>
            <th>П</th>
            <th>Коэф</th>
        </tr>
        </thead>
        <tbody>
        @foreach($standings as $index => $teamStats)
            <tr class="@if($index < 2) qualification-zone @elseif($index >= count($standings) - 2) relegation-zone @endif">
                <td class="fw-bold">{{ $index + 1 }}</td>
                <td class="team-name">{{ $teamStats['team_name'] }}</td>

                <!-- Результаты против каждой команды -->
                @foreach($standings as $opponentIndex => $opponentStats)
                    @if($teamStats['team']->id === $opponentStats['team']->id)
                        <td class="result-cell" style="background: silver;" align="center">🏐</td>
                    @else
                        @php
                            $result = $teamStats['results'][$opponentStats['team']->id] ?? null;
                            $gameDetails = $getGameDetails($games, $teamStats['team']->id, $opponentStats['team']->id);
                        @endphp
                        @if($result)
                            <td class="result-cell {{ $result['class'] }}"
                                @if($gameDetails)
                                    data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="{{ $gameDetails }}"
                                @endif
                            >
                                {{ $result['score'] }}
                            </td>
                        @else
                            <td class="result-cell text-muted">-:-</td>
                        @endif
                    @endif
                @endforeach

                <!-- Статистика -->
                <td class="fw-bold">{{ $teamStats['games_played'] }}</td>
                <td class="fw-bold text-success">{{ $teamStats['games_won'] }}</td>
                <td class="fw-bold text-danger">{{ $teamStats['games_lost'] }}</td>
                <td class="fw-bold" style="background: #e3f2fd;">{{ $teamStats['points'] }}</td>
                <td class="fw-bold">{{ $teamStats['sets_won'] }}</td>
                <td class="fw-bold">{{ $teamStats['sets_lost'] }}</td>
                <td class="fw-bold">{{ $teamStats['sets_ratio'] }}</td>
                <td class="fw-bold">{{ $teamStats['points_won'] }}</td>
                <td class="fw-bold">{{ $teamStats['points_lost'] }}</td>
                <td class="fw-bold">{{ $teamStats['points_ratio'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@if($standings->isEmpty())
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle me-2"></i>В этой группе пока нет статистики.
    </div>
@endif
