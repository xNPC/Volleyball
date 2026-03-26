<x-app-layout>
    <div class="container py-4">
        <!-- Хлебные крошки (оставляем как есть) -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Главная</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tournaments.index') }}" class="text-decoration-none">Турниры</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tournaments.show', $tournament) }}" class="text-decoration-none">{{ $tournament->name }}</a></li>
                <li class="breadcrumb-item active">{{ $stage->name }}</li>
            </ol>
        </nav>

        <!-- Заголовок -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-volleyball">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="display-6 fw-bold" style="color: var(--volleyball-blue);">
                                    <i class="fas fa-trophy me-3"></i>{{ $stage->name }}
                                </h1>
                                <p class="lead mb-0">Плейофф турнира: {{ $tournament->name }}</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="score-badge">Групп: {{ $groupsWithBrackets->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Табы групп -->
        @if($groupsWithBrackets->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="card card-volleyball">
                        <div class="card-header bg-transparent border-bottom-0 p-0">
                            <ul class="nav nav-tabs" id="groupsTab" role="tablist">
                                @foreach($groupsWithBrackets as $index => $group)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                                id="group-{{ $group->id }}-tab"
                                                data-bs-toggle="tab"
                                                data-bs-target="#group-{{ $group->id }}"
                                                type="button"
                                                role="tab">
                                            <i class="fas fa-users me-2"></i>{{ $group->name }}
                                            <span class="badge bg-secondary ms-2">{{ $group->teams->count() }} команд</span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="groupsTabContent">
                                @foreach($groupsWithBrackets as $index => $group)
                                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                                         id="group-{{ $group->id }}"
                                         role="tabpanel">

                                        <!-- Внутренние табы для группы: Сетка и Список игр -->
                                        <ul class="nav nav-pills mb-4" id="group-{{ $group->id }}-subtab" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active"
                                                        id="bracket-{{ $group->id }}-tab"
                                                        data-bs-toggle="pill"
                                                        data-bs-target="#bracket-{{ $group->id }}"
                                                        type="button">
                                                    <i class="fas fa-project-diagram me-2"></i>Сетка турнира
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link"
                                                        id="games-{{ $group->id }}-tab"
                                                        data-bs-toggle="pill"
                                                        data-bs-target="#games-{{ $group->id }}"
                                                        type="button">
                                                    <i class="fas fa-list me-2"></i>Список игр
                                                </button>
                                            </li>
                                        </ul>

                                        <div class="tab-content">
                                            <!-- Вкладка с сеткой -->
                                            <div class="tab-pane fade show active" id="bracket-{{ $group->id }}">
                                                @include('components.playoff-bracket-flexible', [
                                                    'bracket' => $group->bracket ?? [],
                                                    'group' => $group
                                                ])
                                            </div>

                                            <!-- Вкладка со списком игр -->
                                            <div class="tab-pane fade" id="games-{{ $group->id }}">
                                                <div class="games-list">
                                                    @php
                                                        $games = $group->games ?? collect();
                                                    @endphp

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
                                                                                    {{ $game->venue->name }}
                                                                                </small>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="alert alert-info text-center">
                                                            <i class="fas fa-info-circle me-2"></i>В этой группе пока нет игр.
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>В этом этапе пока нет групп.
            </div>
        @endif
    </div>
    <style>
        .nav-tabs .nav-link {
            color: var(--volleyball-blue);
            font-weight: 600;
            border: none;
            border-radius: 0;
            padding: 12px 20px;
        }

        .nav-tabs .nav-link.active {
            background: var(--volleyball-orange);
            color: white;
            border: none;
        }

        .nav-pills .nav-link {
            color: var(--volleyball-blue);
            font-weight: 500;
            margin-right: 10px;
        }

        .nav-pills .nav-link.active {
            background: var(--volleyball-blue);
            color: white;
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

        .card-volleyball {
            background: white;
            box-shadow: 0 8px 25px rgba(0, 78, 137, 0.15);
            transition: all 0.3s ease;
        }

        .card-volleyball:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(255, 107, 53, 0.2);
        }

        .score-badge {
            background: var(--volleyball-orange);
            color: white;
            font-weight: bold;
            font-size: 1.1em;
            padding: 8px 15px;
            border-radius: 20px;
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
    </style>
</x-app-layout>
