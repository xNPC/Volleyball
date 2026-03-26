@if(empty($bracket))
    <div class="alert alert-warning">
        Нет данных сетки для группы {{ $group->name ?? '?' }}
    </div>
@endif
@props(['bracket', 'stage'])

<div class="playoff-bracket-container">
    @forelse($bracket as $roundIndex => $round)
        <div class="bracket-round" data-round="{{ $round['round_number'] ?? $roundIndex + 1 }}">
            <div class="round-header">
                <h4 class="round-title">{{ $round['round_name'] ?? 'Раунд ' . ($roundIndex + 1) }}</h4>
                <span class="round-badge">{{ count($round['matches'] ?? []) }} матчей</span>
            </div>

            <div class="matches-container">
                @foreach(($round['matches'] ?? []) as $match)
                    @php
                        $winner = $match['winner'] ?? null;

                        // Получаем названия команд
                        $homeTeamName = 'TBD';
                        $awayTeamName = 'TBD';
                        $homeScore = '-';
                        $awayScore = '-';

                        // Домашняя команда
                        if (!empty($match['home_team'])) {
                            if (is_array($match['home_team'])) {
                                $homeTeamName = $match['home_team']['name'] ?? 'TBD';
                            } else {
                                $homeTeamName = $match['home_team']->name ?? 'TBD';
                            }
                        }

                        // Гостевая команда
                        if (!empty($match['away_team'])) {
                            if (is_array($match['away_team'])) {
                                $awayTeamName = $match['away_team']['name'] ?? 'TBD';
                            } else {
                                $awayTeamName = $match['away_team']->name ?? 'TBD';
                            }
                        }

                        // Счета
                        if (isset($match['home_score']) && $match['home_score'] !== null) {
                            $homeScore = $match['home_score'];
                        }
                        if (isset($match['away_score']) && $match['away_score'] !== null) {
                            $awayScore = $match['away_score'];
                        }

                        $homeWins = $match['home_wins'] ?? 0;
                        $awayWins = $match['away_wins'] ?? 0;
                        $status = $match['status'] ?? 'scheduled';
                    @endphp

                    <div class="match-series-card" data-match="{{ $match['match_number'] ?? $loop->index + 1 }}">
                        @if(isset($match['title']))
                            <div class="match-title">{{ $match['title'] }}</div>
                        @endif
                        <div class="match-header">
                            <span class="match-number">Матч #{{ $match['match_number'] ?? $loop->index + 1 }}</span>
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
                                    <span class="team-score">{{ $homeScore }}</span>
                                </div>
                                <div class="vs-divider">:</div>
                                <div class="team-stat {{ $winner === 'away' ? 'winner' : '' }}">
                                    <span class="team-score">{{ $awayScore }}</span>
                                    <span class="team-name">{{ $awayTeamName }}</span>
                                </div>
                            </div>

                            @if(!empty($match['sets']) && count($match['sets']) > 0)
                                <div class="games-list">
                                    @foreach($match['sets'] as $set)
                                        <div class="game-item">
                                            <span class="game-number">Сет {{ $set['set_number'] ?? $loop->index + 1 }}</span>
                                            <span class="game-score">
                                {{ $set['home_score'] ?? 0 }}:{{ $set['away_score'] ?? 0 }}
                            </span>
                                        </div>
                                    @endforeach
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
                @endforeach
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

    .vs-divider {
        font-size: 1.5rem;
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
    }

    .game-item.completed {
        background: rgba(0, 78, 137, 0.05);
    }

    .game-number {
        min-width: 60px;
        color: #666;
    }

    .game-score {
        font-weight: bold;
        color: var(--volleyball-blue);
    }

    .game-sets {
        color: #666;
        font-size: 0.8rem;
    }

    .next-match-info {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed #ddd;
        color: #666;
    }

    .match-title {
        font-size: 0.8rem;
        font-weight: bold;
        color: var(--volleyball-orange);
        text-align: center;
        margin-bottom: 8px;
        text-transform: uppercase;
    }
</style>
