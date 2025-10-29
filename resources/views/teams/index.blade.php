<x-app-layout>
    <div class="container py-4">
        <!-- Хлебные крошки -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Главная</a></li>
                <li class="breadcrumb-item active">Команды</li>
            </ol>
        </nav>

        <!-- Заголовок -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-5 fw-bold text-center mb-3" style="color: var(--volleyball-blue);">
                    <i class="fas fa-users me-3"></i>Команды
                </h1>
                <p class="text-center text-muted">Список всех зарегистрированных команд</p>
            </div>
        </div>

        <!-- Статистика -->
{{--        <div class="row mb-4">--}}
{{--            <div class="col-md-4 col-6">--}}
{{--                <div class="card card-volleyball text-center">--}}
{{--                    <div class="card-body">--}}
{{--                        <h3 class="fw-bold" style="color: var(--volleyball-orange);">{{ $stats['total_teams'] }}</h3>--}}
{{--                        <p class="mb-0 text-muted">Всего команд</p>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-md-4 col-6">--}}
{{--                <div class="card card-volleyball text-center">--}}
{{--                    <div class="card-body">--}}
{{--                        <h3 class="fw-bold" style="color: var(--volleyball-orange);">--}}
{{--                            {{ $stats['new_teams_month'] }}--}}
{{--                        </h3>--}}
{{--                        <p class="mb-0 text-muted">Новых за месяц</p>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-md-4 col-6">--}}
{{--                <div class="card card-volleyball text-center">--}}
{{--                    <div class="card-body">--}}
{{--                        <h3 class="fw-bold" style="color: var(--volleyball-orange);">--}}
{{--                            {{ $stats['teams_with_tournaments'] }}--}}
{{--                        </h3>--}}
{{--                        <p class="mb-0 text-muted">Участвуют в турнирах</p>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

        <!-- Поиск и фильтры -->
        <div class="card card-volleyball mb-4">
            <div class="card-body">
                <form action="{{ route('teams.index') }}" method="GET">
                    <div class="row align-items-center">
                        <div class="col-md-8 mb-3 mb-md-0">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text"
                                       name="search"
                                       class="form-control border-start-0"
                                       placeholder="Поиск по названию команды..."
                                       value="{{ $search }}">
                                @if($search)
                                    <a href="{{ route('teams.index') }}" class="input-group-text bg-transparent border-start-0 text-muted" title="Очистить поиск">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
{{--                        <div class="col-md-4 mb-3 mb-md-0">--}}
{{--                            <select name="filter" class="form-select" onchange="this.form.submit()">--}}
{{--                                <option value="all" {{ $filter == 'all' ? 'selected' : '' }}>Все команды</option>--}}
{{--                                <option value="with_tournaments" {{ $filter == 'with_tournaments' ? 'selected' : '' }}>Участвуют в турнирах</option>--}}
{{--                                <option value="new" {{ $filter == 'new' ? 'selected' : '' }}>Новые команды</option>--}}
{{--                            </select>--}}
{{--                        </div>--}}
                        <div class="col-md-4 text-end">
                            <button type="submit" class="btn btn-volleyball w-100">
                                <i class="fas fa-filter me-1"></i>Найти
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Результаты поиска -->
        @if($search || $filter != 'all')
            <div class="alert alert-info mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-info-circle me-2"></i>
                        @if($search && $filter != 'all')
                            Найдено {{ $teams->total() }} команд по запросу "{{ $search }}" с фильтром "{{ $filter }}"
                        @elseif($search)
                            Найдено {{ $teams->total() }} команд по запросу "{{ $search }}"
                        @else
                            Показаны команды с фильтром "{{ $filter }}": {{ $teams->total() }} результатов
                        @endif
                    </div>
                    <a href="{{ route('teams.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Сбросить
                    </a>
                </div>
            </div>
        @endif

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

                            <!-- Статистика -->
                            <div class="team-stats mb-3">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="stat-number fw-bold" style="color: var(--volleyball-orange);">
                                            {{ $team->active_tournaments_count }}
                                        </div>
                                        <div class="stat-label small text-muted">Турниров</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-number fw-bold" style="color: var(--volleyball-orange);">
                                            {{ $team->applications_count }}
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

                            <!-- Кнопка -->
                            <a href="{{ route('teams.show', $team) }}"
                               class="btn btn-volleyball btn-sm w-100">
                                <i class="fas fa-eye me-1"></i>Профиль команды
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">
                            @if($search || $filter != 'all')
                                Команды не найдены
                            @else
                                Команды не найдены
                            @endif
                        </h4>
                        <p class="text-muted">
                            @if($search || $filter != 'all')
                                Попробуйте изменить параметры поиска или сбросить фильтры
                            @else
                                Здесь появятся команды, когда они будут созданы
                            @endif
                        </p>
                        @if($search || $filter != 'all')
                            <a href="{{ route('teams.index') }}" class="btn btn-volleyball">
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
            //border: 2px solid var(--volleyball-orange);
        }

        .user-avatar-xs {
            width: 24px;
            height: 24px;
        }
    </style>
</x-app-layout>
