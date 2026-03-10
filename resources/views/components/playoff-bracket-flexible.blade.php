@props(['bracket', 'stage'])

<div class="playoff-bracket-container">
    @foreach($bracket as $roundIndex => $round)
        <div class="bracket-round" data-round="{{ $round['round_number'] }}">
            <div class="round-header">
                <h4 class="round-title">{{ $round['round_name'] }}</h4>
                <span class="round-badge">{{ count($round['matches']) }} матчей</span>
            </div>

            <div class="matches-container">
                @foreach($round['matches'] as $match)
                    <div class="match-series-card" data-match="{{ $match['match_number'] }}">
                        <div class="match-header">
                            <span class="match-number">Матч #{{ $match['match_number'] }}</span>
                            @if($match['status'] === 'completed')
                                <span class="badge bg-success">Завершен</span>
                            @else
                                <span class="badge bg-warning">Ожидание</span>
                            @endif
                        </div>

                        <div class="series-info">
                            <div class="series-stats">
                                <div class="team-stat {{ $match['winner'] === 'home' ? 'winner' : '' }}">
                                    <span class="team-name">{{ $match['home_team']?->team->name ?? 'TBD' }}</span>
                                    <span class="team-wins">{{ $match['home_wins'] }}</span>
                                </div>
                                <div class="vs-divider">:</div>
                                <div class="team-stat {{ $match['winner'] === 'away' ? 'winner' : '' }}">
                                    <span class="team-wins">{{ $match['away_wins'] }}</span>
                                    <span class="team-name">{{ $match['away_team']?->team->name ?? 'TBD' }}</span>
                                </div>
                            </div>

                            @if($round['type'] === 'series')
                                <div class="games-list">
                                    @foreach($match['games'] as $game)
                                        <div class="game-item {{ $game['status'] === 'completed' ? 'completed' : '' }}">
                                            <span class="game-number">Игра {{ $game['game_number'] }}</span>
                                            @if($game['status'] === 'completed')
                                                <span class="game-score">
                                                    {{ $game['home_score'] }}:{{ $game['away_score'] }}
                                                </span>
                                                @if(!empty($game['sets']))
                                                    <span class="game-sets">
                                                        ({{ collect($game['sets'])->map(fn($s) => "{$s['home_score']}:{$s['away_score']}")->implode(', ') }})
                                                    </span>
                                                @endif
                                            @else
                                                <span class="game-status">vs</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        @if(isset($match['next_match']))
                            <div class="next-match-info">
                                <small>→ Победитель идет в:
                                    @if($match['next_match']['type'] === 'champion')
                                        <strong>Чемпион</strong>
                                    @else
                                        Матч {{ $match['next_match']['match'] }} ({{ $match['next_match']['position'] === 'home' ? 'хозяева' : 'гости' }})
                                    @endif
                                </small>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<style>
    .playoff-bracket-container {
        display: flex;
        gap: 40px;
        overflow-x: auto;
        padding: 20px;
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
    }

    .team-stat.winner {
        background: rgba(40, 167, 69, 0.1);
        font-weight: bold;
    }

    .team-wins {
        font-size: 1.5rem;
        font-weight: bold;
        color: var(--volleyball-orange);
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
</style>
