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
                        Организация и проведение волейбольных соревнований.
                        Присоединяйтесь к лучшим командам и соревнуйтесь за звание чемпиона!
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <button class="btn btn-volleyball btn-lg">
                            <i class="fas fa-trophy me-2"></i>Участвовать
                        </button>
                        <button class="btn btn-outline-light btn-lg">
                            <i class="fas fa-info-circle me-2"></i>Подробнее
                        </button>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-volleyball-ball fa-10x text-white-50"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Tournaments -->
    <section id="tournaments" class="py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3" style="color: var(--volleyball-blue);">
                    <i class="fas fa-trophy me-2"></i>Ближайшие Турниры
                </h2>
                <p class="lead text-muted">Примите участие в самых ожидаемых соревнованиях сезона</p>
            </div>

            <div class="row g-4">
                @foreach($featuredTournaments as $tournament)
                    <div class="col-md-4">
                        <div class="card-volleyball tournament-card h-100 p-4">
                            <div class="text-center mb-3">
                                <i class="fas fa-trophy net-icon"></i>
                            </div>
                            <h5 class="fw-bold text-center mb-3" style="color: var(--volleyball-blue);">
                                {{ $tournament['name'] }}
                            </h5>
                            <div class="tournament-info">
                                <p class="mb-2">
                                    <i class="fas fa-users me-2 text-muted"></i>
                                    {{ $tournament['teams_count'] }} команд
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                                    {{ $tournament['location'] }}
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-calendar me-2 text-muted"></i>
                                    {{ $tournament['date'] }}
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-gem me-2 text-muted"></i>
                                    Приз: {{ $tournament['prize'] }}
                                </p>
                            </div>
                            <div class="text-center mt-4">
                                <button class="btn btn-volleyball btn-sm">
                                    Зарегистрироваться
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Upcoming Matches -->
    <section id="schedule" class="py-5 court-bg text-white">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">
                    <i class="fas fa-volleyball-ball me-2"></i>Ближайшие Матчи
                </h2>
                <p class="lead">Не пропустите захватывающие противостояния</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    @foreach($upcomingMatches as $match)
                        <div class="match-card court-lines">
                            <div class="row align-items-center">
                                <div class="col-4 text-end">
                                    <div class="team-card">
                                        <div class="team-logo">
                                            {{ substr($match['team1'], 0, 2) }}
                                        </div>
                                        <h6 class="fw-bold text-dark">{{ $match['team1'] }}</h6>
                                    </div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="score-badge">VS</div>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>{{ $match['time'] }}
                                        </small>
                                        <br>
                                        <small class="text-muted">{{ $match['date'] }}</small>
                                        <br>
                                        <small class="text-muted">{{ $match['court'] }}</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="team-card">
                                        <div class="team-logo">
                                            {{ substr($match['team2'], 0, 2) }}
                                        </div>
                                        <h6 class="fw-bold text-dark">{{ $match['team2'] }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Top Teams -->
    <section id="teams" class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3" style="color: var(--volleyball-blue);">
                    <i class="fas fa-medal me-2"></i>Лучшие Команды
                </h2>
                <p class="lead text-muted">Рейтинг сильнейших коллективов сезона</p>
            </div>

            <div class="row g-4 justify-content-center">
                @foreach($topTeams as $index => $team)
                    <div class="col-md-6 col-lg-3">
                        <div class="card-volleyball text-center p-4">
                            <div class="position-relative">
                                <div class="team-logo mx-auto mb-3">
                                    {{ $team['logo'] }}
                                </div>
                                @if($index < 3)
                                    <div class="position-absolute top-0 start-50 translate-middle">
                                        <i class="fas fa-crown text-warning fa-lg"></i>
                                    </div>
                                @endif
                            </div>
                            <h5 class="fw-bold mb-2">{{ $team['name'] }}</h5>
                            <div class="d-flex justify-content-center align-items-center">
                            <span class="badge bg-success me-2">
                                <i class="fas fa-check me-1"></i>{{ $team['wins'] }} побед
                            </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5">
        <div class="container py-5">
            <div class="card-volleyball p-5 text-center">
                <h2 class="display-6 fw-bold mb-3" style="color: var(--volleyball-blue);">
                    Готовы к игре?
                </h2>
                <p class="lead text-muted mb-4">
                    Создайте свою команду и начните путь к чемпионству!
                </p>
                <button class="btn btn-volleyball btn-lg px-5">
                    <i class="fas fa-plus me-2"></i>Создать команду
                </button>
            </div>
        </div>
    </section>
</div>
