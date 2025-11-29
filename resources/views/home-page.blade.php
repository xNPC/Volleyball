<x-app-layout>
    <div>
        <!-- Hero Section -->
        <section class="volleyball-bg text-white py-5">
            <div class="container py-5">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <h1 class="display-4 fw-bold mb-4">
                            Волейбольные <span class="text-warning">Турниры</span>
                        </h1>
                        <p class="lead mb-4">
                            Чемпионат города Кемерово по волейболу среди мужских команд
                        </p>
                        <div class="d-flex gap-3 flex-wrap">
                            <a href="/tournaments" class="btn btn-volleyball btn-lg">
                                <i class="fas fa-trophy me-2"></i>Смотреть турниры
                            </a>
                            <a href="{{ route('teams.index') }}" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-users me-2"></i>Все команды
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 text-center">
                        <i class="fas fa-volleyball-ball fa-10x text-white-50"></i>
                    </div>
                </div>
            </div>
        </section>

        <!-- Matches Section -->
        <section id="matches" class="py-5 bg-light">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3" style="color: var(--volleyball-blue);">
                        <i class="fas fa-volleyball-ball me-2"></i>Матчи
                    </h2>
                    <p class="lead text-muted">Следите за результатами и предстоящими играми</p>
                </div>

                <div class="row">
                    <!-- Ближайшие матчи -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-primary text-white py-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-clock me-2"></i>Ближайшие матчи
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                @forelse($upcomingMatches as $match)
                                    <div class="border-bottom p-3">
                                        <div class="match-grid mb-2">
                                            <div class="team team-left text-end">
                                                <span class="fw-bold">{{ $match['team1_name'] }}</span>
                                            </div>

                                            <div class="vs-badge">
                                                <span class="vs-text">VS</span>
                                            </div>

                                            <div class="team team-right text-start">
                                                <span class="fw-bold">{{ $match['team2_name'] }}</span>
                                            </div>
                                        </div>

                                        <div class="text-muted small mb-1 text-center">
                                            <i class="fas fa-clock me-1"></i>{{ $match['time'] }} •
                                            <i class="fas fa-calendar me-1"></i>{{ $match['date'] }}
                                        </div>
                                        <div class="text-muted small mb-1 text-center">
                                            <i class="fas fa-map-marker-alt me-1"></i>{{ $match['location'] ?? 'Не указано' }}
                                        </div>
                                        <div class="text-muted small text-center">
                                            <i class="fas fa-trophy me-1"></i>{{ $match['tournament'] }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4">
                                        <p class="text-muted mb-0">Нет предстоящих матчей</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Прошедшие матчи -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-success text-white py-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-history me-2"></i>Прошедшие матчи
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                @forelse($pastMatches as $match)
                                    <div class="border-bottom p-3">
                                        <!-- Основная строка с командами и счетом -->
                                        <div class="match-grid mb-2">
                                            <div class="team team-left text-end">
                                                <span class="fw-bold @if($match['winner'] === 'team1') text-success @endif">
                                                    {{ $match['team1_name'] }}
                                                </span>
                                            </div>

                                            <div class="score">
                                                <span class="fs-4 fw-bold text-primary">
                                                    {{ $match['score'] }}
                                                </span>
                                            </div>

                                            <div class="team team-right text-start">
                                                <span class="fw-bold @if($match['winner'] === 'team2') text-success @endif">
                                                    {{ $match['team2_name'] }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="text-muted small mb-1 text-center">
                                            <i class="fas fa-clock me-1"></i>{{ $match['time'] }} •
                                            <i class="fas fa-calendar me-1"></i>{{ $match['date'] }}
                                        </div>

                                        <!-- Дополнительная информация -->
                                        <div class="text-muted small mb-1 text-center">
                                            {{ $match['sets'] }}
                                        </div>
                                        <div class="text-muted small text-center">
                                            <i class="fas fa-trophy me-1"></i>{{ $match['tournament'] }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4">
                                        <p class="text-muted mb-0">Нет данных о прошедших матчах</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Top Teams -->
        <section id="teams" class="py-5">
            <div class="container py-5">
                <div class="text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3" style="color: var(--volleyball-blue);">
                        <i class="fas fa-medal me-2"></i>Лучшие Команды
                    </h2>
                    <p class="lead text-muted">Рейтинг сильнейших коллективов сезона</p>
                </div>

                <div class="row g-4 justify-content-center">
                    @forelse($topTeams as $index => $team)
                        <div class="col-md-6 col-lg-3">
                            <div class="card text-center h-100 border-0 shadow-sm">
                                <div class="card-body py-4">
                                    <div class="position-relative mb-3">
                                        <div class="bg-volleyball-blue text-white rounded-circle mx-auto d-flex align-items-center justify-content-center"
                                             style="width: 60px; height: 60px;">
                                            <span class="fw-bold">{{ $team['logo'] }}</span>
                                        </div>
                                        @if($index < 3)
                                            <div class="position-absolute top-0 start-50 translate-middle">
                                                <i class="fas fa-crown text-warning"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <h5 class="fw-bold mb-2">{{ $team['name'] }}</h5>
                                    <div class="d-flex justify-content-center align-items-center">
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>{{ $team['wins'] }} побед
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center">
                            <p class="text-muted">Нет данных о командах</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <!-- Birthday Section -->
        <section id="birthdays" class="py-5 court-bg text-white">
            <div class="container py-5">
                <div class="text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3">
                        <i class="fas fa-birthday-cake me-2"></i>Дни рождения сегодня
                    </h2>
                    <p class="lead">Поздравьте наших игроков!</p>
                </div>

                @if($birthdayUsers->count() > 0)
                    <div class="row g-4 justify-content-center">
                        @foreach($birthdayUsers as $user)
                            <div class="col-md-6 col-lg-3">
                                <div class="card text-center h-100 border-0 shadow">
                                    <div class="card-body py-4">
                                        <div class="position-relative mb-3">
                                            @if($user->profile_photo_url)
                                                <img src="{{ $user->profile_photo_url }}"
                                                     alt="{{ $user->name }}"
                                                     class="rounded-circle mx-auto"
                                                     style="width: 80px; height: 80px; object-fit: cover;">
                                            @else
                                                <div class="bg-volleyball-orange rounded-circle mx-auto d-flex align-items-center justify-content-center"
                                                     style="width: 80px; height: 80px;">
                                                    <span class="text-white fw-bold fs-4">
                                                        {{ substr($user->name, 0, 1) }}
                                                    </span>
                                                </div>
                                            @endif
                                            <div class="position-absolute top-0 end-0">
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-birthday-cake"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-2">{{ $user->name }}</h5>
{{--                                        <p class="text-muted mb-2">--}}
{{--                                            <i class="fas fa-volleyball-ball me-1"></i>--}}
{{--                                            {{ $user->position }}--}}
{{--                                        </p>--}}
                                        <p class="text-muted mb-3">
                                            <i class="fas fa-cake-candles me-1"></i>
                                            Исполняется {{ (int)$user->age }} лет
                                        </p>
{{--                                        <button class="btn btn-sm btn-volleyball">--}}
{{--                                            <i class="fas fa-gift me-1"></i>Поздравить--}}
{{--                                        </button>--}}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-birthday-cake fa-3x text-white-50 mb-3"></i>
                        <p class="text-white mb-2">Сегодня нет дней рождения</p>
                        <p class="text-white-50">Возвращайтесь завтра!</p>
                    </div>
                @endif
            </div>
        </section>
    </div>

    <style>
        .bg-volleyball-orange {
            background-color: var(--volleyball-orange);
        }
        .bg-volleyball-blue {
            background-color: var(--volleyball-blue);
        }

        .match-grid {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 1rem;
        }

        .team-left {
            text-align: right;
        }

        .team-right {
            text-align: left;
        }

        .score {
            text-align: center;
            min-width: 80px;
        }

        .vs-badge {
            text-align: center;
            min-width: 70px;
        }

        .vs-text {
            display: inline-block;
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }
    </style>
</x-app-layout>
