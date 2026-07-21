@if(empty($bracket))
    <div class="alert alert-warning">
        Нет данных сетки для группы {{ $group->name ?? '?' }}
    </div>
@endif
@props(['bracket', 'group'])

<div class="playoff-bracket-container">
    <!-- ОТЛАДКА -->
    @php
        //\Log::info('=== VIEW BRACKET ===');
        //\Log::info('Bracket rounds: ' . count($bracket));
        foreach ($bracket as $ri => $round) {
            //\Log::info('Round ' . ($ri+1) . ': ' . ($round['round_name'] ?? 'no name') . ', matches: ' . count($round['matches'] ?? []));
            foreach (($round['matches'] ?? []) as $mi => $match) {
                //\Log::info('  Match ' . ($mi+1) . ': home=' . (isset($match['home_team']['name']) ? $match['home_team']['name'] : (isset($match['home_team']) ? 'object' : 'null')) . ', away=' . (isset($match['away_team']['name']) ? $match['away_team']['name'] : (isset($match['away_team']) ? 'object' : 'null')));
            }
        }
    @endphp
    @forelse($bracket as $roundIndex => $round)
        <div class="bracket-round" data-round="{{ $round['round_number'] ?? $roundIndex + 1 }}">
            <div class="round-header">
                <h4 class="round-title">
                    @if(isset($round['round_name']))
                        {{ $round['round_name'] }}
                    @else
                        {{ $roundIndex === 0 ? 'Первый раунд' : 'Раунд ' . ($roundIndex + 1) }}
                    @endif
                </h4>
                <!--<span class="round-badge">{{ count($round['matches'] ?? []) }} матчей</span>-->
            </div>

            <div class="matches-container">
                @forelse(($round['matches'] ?? []) as $match)
                    @php
                        $matchFormat = $match['match_format'] ?? 'single';
                        $games = $match['games'] ?? [];
                        $homeWins = $match['home_wins'] ?? 0;
                        $awayWins = $match['away_wins'] ?? 0;
                        $status = $match['status'] ?? 'pending';
                        $winner = $match['winner'] ?? null;

                        $homeTeamName = 'TBD';
                        $awayTeamName = 'TBD';

                        if (!empty($match['home_team'])) {
                            $homeTeamName = is_array($match['home_team'])
                                ? ($match['home_team']['name'] ?? 'TBD')
                                : ($match['home_team']->name ?? 'TBD');
                        }
                        if (!empty($match['away_team'])) {
                            $awayTeamName = is_array($match['away_team'])
                                ? ($match['away_team']['name'] ?? 'TBD')
                                : ($match['away_team']->name ?? 'TBD');
                        }
                    @endphp

                    <div class="match-series-card" data-match="{{ $match['match_number'] ?? $loop->index + 1 }}">
                        <div class="match-header">
                            <span class="match-number">#{{ $match['match_number'] ?? $loop->index + 1 }}</span>
                            @if(isset($match['title']))
                                <span class="match-title">{{ $match['title'] }}</span>
                            @endif
                            @if($matchFormat === 'best_of_3')
                                <span class="badge bg-info">До 2 побед</span>
                            @endif
                            @if($status === 'completed')
                                <span class="badge bg-success">Завершен</span>
                            @elseif($status === 'scheduled')
                                <span class="badge bg-warning">Запланирован</span>
                            @else
                                <span class="badge bg-secondary">Ожидание</span>
                            @endif
                        </div>

                        <div class="series-info">
                            <div class="series-stats">
                                <div class="team-stat {{ $winner === 'home' ? 'winner' : '' }}">
                                    <span class="team-name">{{ $homeTeamName }}</span>
                                    <span class="team-wins">{{ $homeWins }}</span>
                                </div>
                                <div class="vs-divider">:</div>
                                <div class="team-stat {{ $winner === 'away' ? 'winner' : '' }}">
                                    <span class="team-wins">{{ $awayWins }}</span>
                                    <span class="team-name">{{ $awayTeamName }}</span>
                                </div>
                            </div>

                            @if(count($games) > 0)
                                <div class="games-list">
                                    @foreach($games as $gameIndex => $game)
                                        @php
                                            $gameHomeName = 'TBD';
                                            $gameAwayName = 'TBD';

                                            if (!empty($game['home_team'])) {
                                                $gameHomeName = is_array($game['home_team'])
                                                    ? ($game['home_team']['name'] ?? 'TBD')
                                                    : ($game['home_team']->name ?? 'TBD');
                                            }
                                            if (!empty($game['away_team'])) {
                                                $gameAwayName = is_array($game['away_team'])
                                                    ? ($game['away_team']['name'] ?? 'TBD')
                                                    : ($game['away_team']->name ?? 'TBD');
                                            }
                                        @endphp
                                        <div class="game-item {{ ($game['home_score'] !== null && $game['away_score'] !== null) ? 'completed' : '' }}">
                                            <span class="game-number">Игра {{ $gameIndex + 1 }}</span>
                                            <span class="game-teams">
                    <span class="game-home">{{ $gameHomeName }}</span>
                    <span class="game-vs">vs</span>
                    <span class="game-away">{{ $gameAwayName }}</span>
                </span>
                                            @if($game['home_score'] !== null && $game['away_score'] !== null)
                                                <span class="game-score">
                        {{ $game['home_score'] }}:{{ $game['away_score'] }}
                    </span>
                                                @if(!empty($game['sets']))
                                                    <span class="game-sets">
                            ({{ collect($game['sets'])->map(fn($s) => $s['home_score'] . ':' . $s['away_score'])->implode(', ') }})
                        </span>
                                                @endif
                                                <span class="game-winner">
                        @if($game['winner'] === 'home')
                                                        <!--<i class="fas fa-check-circle text-success"></i>-->
                                                    @elseif($game['winner'] === 'away')
                                                        <!--<i class="fas fa-check-circle text-success"></i>-->
                                                    @endif
                    </span>
                                            @else
                                                <span class="game-status">Ожидание</span>
                                            @endif
                                        </div>
                                    @endforeach

                                    {{-- РЕШАЮЩАЯ ИГРА - только когда сыграно 2 игры и счет 1:1 (ничья) --}}
                                    @php
                                        $playedGames = array_filter($games, function($g) {
                                            return $g['home_score'] !== null && $g['away_score'] !== null;
                                        });
                                        $playedCount = count($playedGames);
                                        $isDraw = ($homeWins == $awayWins);
                                    @endphp

                                    @if($matchFormat === 'best_of_3' && $playedCount == 2 && $isDraw && $status !== 'completed' && $status !== 'scheduled')
                                        <div class="game-item deciding-game">
                                            <span class="game-number">Решающая игра</span>
                                            <span class="game-status text-warning">Ожидание</span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if(!empty($match['next_match']))
                            <div class="next-match-info">
                                <small>→ Победитель идет в:
                                    @if(($match['next_match']['type'] ?? '') === 'champion')
                                        <strong>Чемпион</strong>
                                    @else
                                        Матч {{ $match['next_match']['match'] ?? '?' }}
                                        ({{ ($match['next_match']['position'] ?? '') === 'home' ? 'хозяева' : 'гости' }})
                                    @endif
                                </small>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="alert alert-info">Нет матчей в этом раунде</div>
                @endforelse
            </div>
        </div>
    @empty
        <div class="alert alert-info w-100">Сетка плейофф еще не сгенерирована</div>
    @endforelse
</div>

<style>
    .playoff-bracket-container {
        display: flex;
        gap: 40px;
        overflow-x: auto;
        padding: 20px;
        min-height: 500px;
    }

    .bracket-round {
        min-width: 350px;
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
    }

    .round-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--volleyball-orange);
    }

    .round-title {
        color: var(--volleyball-blue);
        margin: 0;
        font-size: 1.2rem;
    }

    .round-badge {
        background: var(--volleyball-orange);
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.9rem;
    }

    .matches-container {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .match-series-card {
        background: white;
        border-left: 4px solid var(--volleyball-blue);
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .match-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        font-size: 0.9rem;
        color: #666;
        flex-wrap: wrap;
        gap: 5px;
    }

    .match-title {
        font-size: 0.8rem;
        font-weight: bold;
        color: var(--volleyball-orange);
        text-align: center;
        text-transform: uppercase;
    }

    .series-stats {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
        margin: 10px 0;
    }

    .team-stat {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 5px 10px;
        border-radius: 5px;
        background: #f8f9fa;
        min-width: 120px;
    }

    .team-stat.winner {
        background: rgba(40, 167, 69, 0.1);
        font-weight: bold;
        border-left: 3px solid #28a745;
    }

    .team-wins {
        font-size: 1.5rem;
        font-weight: bold;
        color: var(--volleyball-orange);
        min-width: 30px;
        text-align: center;
    }

    .team-score {
        font-size: 1.2rem;
        font-weight: bold;
        color: var(--volleyball-blue);
    }

    .vs-divider {
        font-size: 1.2rem;
        font-weight: bold;
        color: #ccc;
    }

    .games-list {
        margin-top: 15px;
        border-top: 1px solid #eee;
        padding-top: 10px;
    }

    .game-item {
        display: flex;
        gap: 10px;
        align-items: center;
        padding: 5px;
        font-size: 0.9rem;
        flex-wrap: wrap;
    }

    .game-item.completed {
        background: rgba(0, 78, 137, 0.05);
    }

    .game-number {
        min-width: 60px;
        color: #666;
    }

    .game-teams {
        display: flex;
        gap: 5px;
        align-items: center;
    }

    .game-home {
        font-weight: 500;
    }

    .game-away {
        font-weight: 500;
    }

    .game-vs {
        color: #999;
        font-size: 0.8rem;
    }

    .game-score {
        font-weight: bold;
        color: var(--volleyball-blue);
    }

    .game-sets {
        color: #666;
        font-size: 0.8rem;
    }

    .game-status {
        color: #999;
    }

    .game-winner {
        margin-left: 5px;
    }

    .next-match-info {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed #ddd;
        color: #666;
    }
</style>
