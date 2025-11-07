@php
    $games = $group->games->load('homeApplication.team', 'awayApplication.team');
@endphp

<div class="games-list">
    @if($games->count() > 0)
        @foreach($games as $game)
            <div class="card match-card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-5 text-end">
                            <div class="d-flex align-items-center justify-content-end">
                                <span class="fw-bold me-3">{{ $game->homeApplication->team->name }}</span>
                                <div class="team-logo-sm">
                                    <i class="fas fa-volleyball-ball"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2 text-center">
                            @if($game->home_score !== null && $game->away_score !== null)
                                <div class="score-display">
                                    <span class="fs-4 fw-bold text-primary">
                                        {{ $game->home_score }}:{{ $game->away_score }}
                                    </span>
                                    @if($game->sets->count() > 0)
                                        <div class="sets-score small text-muted">
                                            Сеты: {{ $game->sets->map(function($set) {
                                                return $set->home_score . ':' . $set->away_score;
                                            })->implode(', ') }}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted">vs</span>
                            @endif
                            <div class="small text-muted mt-1">
                                {{ $game->scheduled_time->format('d.m.Y H:i') }}
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="d-flex align-items-center">
                                <div class="team-logo-sm me-3">
                                    <i class="fas fa-volleyball-ball"></i>
                                </div>
                                <span class="fw-bold">{{ $game->awayApplication->team->name }}</span>
                            </div>
                        </div>
                    </div>

                    @if($game->venue)
                        <div class="row mt-2">
                            <div class="col-12 text-center">
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    {{ $game->venue->name }}, {{ $game->venue->address }}
                                </small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle me-2"></i>В этой группе пока нет запланированных игр.
        </div>
    @endif
</div>

<style>
    .technical-defeat {
        color: #ff0000 !important;
        font-weight: bold;
        text-decoration: line-through;
    }

    .team-logo-sm {
        width: 30px;
        height: 30px;
        background: var(--volleyball-blue);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.8em;
    }

    .match-card {
        border-left: 4px solid var(--volleyball-orange);
        transition: transform 0.2s;
    }

    .match-card:hover {
        transform: translateX(5px);
    }
</style>
