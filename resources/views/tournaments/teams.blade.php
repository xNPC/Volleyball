<x-app-layout>
    <div class="container py-4">
        <!-- Хлебные крошки -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Главная</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tournaments.index') }}" class="text-decoration-none">Турниры</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tournaments.show', $tournament) }}" class="text-decoration-none">{{ $tournament->name }}</a></li>
                <li class="breadcrumb-item active">Команды</li>
            </ol>
        </nav>

        <!-- Заголовок турнира -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-volleyball">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="display-6 fw-bold mb-2" style="color: var(--volleyball-blue);">
                                    <i class="fas fa-users me-3"></i>Команды турнира
                                </h1>
                                <h2 class="h4 text-muted">{{ $tournament->name }}</h2>
                                <div class="d-flex gap-4 text-muted mt-2">
                                    <span>
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        {{ $tournament->start_date->format('d.m.Y') }} - {{ $tournament->end_date->format('d.m.Y') }}
                                    </span>
                                    <span>
                                        <i class="fas fa-users me-1"></i>
                                        Команд: {{ $stats['total_teams'] }}
                                    </span>
                                    @if($stats['pending_applications'] > 0)
                                        <span>
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $stats['pending_applications'] }} заявок на рассмотрении
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="score-badge d-inline-block">
                                    <i class="fas fa-volleyball-ball me-2"></i>Активен
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Поиск -->
        <div class="card card-volleyball mb-4">
            <div class="card-body">
                <form action="{{ route('tournaments.teams', $tournament) }}" method="GET">
                    <div class="row align-items-center">
                        <div class="col-md-8 mb-3 mb-md-0">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text"
                                       name="search"
                                       class="form-control border-start-0"
                                       placeholder="Поиск по названию команды, городу или описанию..."
                                       value="{{ $search }}">
                                @if($search)
                                    <a href="{{ route('tournaments.teams', $tournament) }}" class="input-group-text bg-transparent border-start-0 text-muted" title="Очистить поиск">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="submit" class="btn btn-volleyball w-100">
                                <i class="fas fa-search me-1"></i>Найти
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Результаты поиска -->
        @if($search)
            <div class="alert alert-info mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-info-circle me-2"></i>
                        Найдено {{ $teams->total() }} команд по запросу "{{ $search }}"
                    </div>
                    <a href="{{ route('tournaments.teams', $tournament) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Сбросить поиск
                    </a>
                </div>
            </div>
        @endif

        <!-- Статистика турнира -->
{{--        <div class="row mb-4">--}}
{{--            <div class="col-md-3 col-6">--}}
{{--                <div class="card card-volleyball text-center">--}}
{{--                    <div class="card-body">--}}
{{--                        <h3 class="fw-bold" style="color: var(--volleyball-orange);">{{ $stats['total_teams'] }}</h3>--}}
{{--                        <p class="mb-0 text-muted">Участников</p>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-md-3 col-6">--}}
{{--                <div class="card card-volleyball text-center">--}}
{{--                    <div class="card-body">--}}
{{--                        <h3 class="fw-bold" style="color: var(--volleyball-orange);">--}}
{{--                            {{ $teams->count() }}--}}
{{--                        </h3>--}}
{{--                        <p class="mb-0 text-muted">На странице</p>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-md-3 col-6">--}}
{{--                <div class="card card-volleyball text-center">--}}
{{--                    <div class="card-body">--}}
{{--                        <h3 class="fw-bold" style="color: var(--volleyball-orange);">--}}
{{--                            {{ $stats['pending_applications'] }}--}}
{{--                        </h3>--}}
{{--                        <p class="mb-0 text-muted">Заявок ожидает</p>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-md-3 col-6">--}}
{{--                <div class="card card-volleyball text-center">--}}
{{--                    <div class="card-body">--}}
{{--                        <h3 class="fw-bold" style="color: var(--volleyball-orange);">--}}
{{--                            {{ round($stats['total_teams'] / 4) }}--}}
{{--                        </h3>--}}
{{--                        <p class="mb-0 text-muted">Групп (примерно)</p>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

        <!-- Список команд -->
        <div class="row g-4">
            @forelse($teams as $team)
                <div class="col-md-6 col-lg-4">
                    <div class="card card-volleyball team-card h-100">
                        <div class="card-body">
                            <!-- Заголовок карточки -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title fw-bold mb-1" style="color: var(--volleyball-blue);">
                                        {{ $team->name }}
                                    </h5>
                                    @if($team->city)
                                        <p class="text-muted small mb-0">
                                            <i class="fas fa-map-marker-alt me-1"></i>{{ $team->city }}
                                        </p>
                                    @endif
                                </div>
                                <div class="team-logo-small">
                                    <i class="fas fa-volleyball-ball"></i>
                                </div>
                            </div>

                            <!-- Описание -->
                            @if($team->description)
                                <p class="card-text text-muted small mb-3">
                                    {{ Str::limit($team->description, 100) }}
                                </p>
                            @endif

                            <!-- Статистика команды -->
                            <div class="team-stats mb-3">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="stat-number fw-bold" style="color: var(--volleyball-orange);">
                                            {{ $team->tournaments_count ?? 0 }}
                                        </div>
                                        <div class="stat-label small text-muted">Турниров</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-number fw-bold" style="color: var(--volleyball-orange);">
                                            {{ $team->applications_count ?? 0 }}
                                        </div>
                                        <div class="stat-label small text-muted">Заявок</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-number fw-bold" style="color: var(--volleyball-orange);">
                                            ?
                                        </div>
                                        <div class="stat-label small text-muted">Игроков</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Капитан -->
                            @if($team->captain)
                                <div class="captain-info mb-3">
                                    <small class="text-muted">Капитан:</small>
                                    <div class="d-flex align-items-center mt-1">
                                        <div class="user-avatar-xs me-2">
                                            @if($team->captain->profile_photo_path)
                                                <img src="{{ asset('storage/' . $team->captain->profile_photo_path) }}"
                                                     alt="{{ $team->captain->name }}"
                                                     class="player-avatar-xs">
                                            @else
                                                <div class="player-avatar-xs bg-secondary text-white d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <span class="small">{{ $team->captain->name }}</span>
                                    </div>
                                </div>
                            @endif

                            <!-- Дата присоединения к турниру -->
                            <div class="tournament-join-date mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-calendar-check me-1"></i>
                                    Участник с: {{ $team->pivot->created_at->format('d.m.Y') }}
                                </small>
                            </div>

                            <!-- Кнопки -->
                            <div class="d-grid gap-2">
                                <a href="{{ route('teams.show', $team) }}"
                                   class="btn btn-volleyball btn-sm">
                                    <i class="fas fa-eye me-1"></i>Профиль команды
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">
                            @if($search)
                                Команды не найдены
                            @else
                                В турнире пока нет команд
                            @endif
                        </h4>
                        <p class="text-muted">
                            @if($search)
                                Попробуйте изменить поисковый запрос
                            @else
                                Команды появятся здесь после одобрения их заявок
                            @endif
                        </p>
                        @if($search)
                            <a href="{{ route('tournaments.teams', $tournament) }}" class="btn btn-volleyball">
                                <i class="fas fa-redo me-1"></i>Показать все команды
                            </a>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Пагинация -->
        @if($teams->hasPages())
            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex justify-content-center">
                        {{ $teams->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <style>
        .team-card {
            transition: all 0.3s ease;
        }

        .team-card:hover {
            transform: translateY(-5px);
        }

        .team-logo-small {
            width: 50px;
            height: 50px;
            background: var(--volleyball-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2em;
        }

        .stat-number {
            font-size: 1.3rem;
        }

        .player-avatar-xs {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--volleyball-orange);
        }

        .user-avatar-xs {
            width: 24px;
            height: 24px;
        }

        .tournament-join-date {
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</x-app-layout>
