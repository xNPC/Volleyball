<x-app-layout>
    <div class="container py-4">
        <!-- Хлебные крошки -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Главная</a></li>
                <li class="breadcrumb-item"><a href="{{ route('teams.index') }}" class="text-decoration-none">Команды</a></li>
                <li class="breadcrumb-item active">{{ $team->name }}</li>
            </ol>
        </nav>

        <!-- Профиль команды -->
        <div class="row">
            <!-- Левая колонка - информация -->
            <div class="col-lg-4 mb-4">
                <div class="card card-volleyball">
                    <div class="card-body text-center">
                        <!-- Логотип -->
                        <div class="team-logo-large mx-auto mb-4">
                            <i class="fas fa-volleyball-ball"></i>
                        </div>

                        <!-- Основная информация -->
                        <h1 class="h3 fw-bold mb-2" style="color: var(--volleyball-blue);">
                            {{ $team->name }}
                        </h1>

                        @if($team->city)
                            <p class="text-muted mb-3">
                                <i class="fas fa-map-marker-alt me-1"></i>{{ $team->city }}
                            </p>
                        @endif

                        @if($team->description)
                            <p class="text-muted mb-4">
                                {{ $team->description }}
                            </p>
                        @endif

                        <!-- Статистика -->
                        <div class="row text-center mb-4">
                            <div class="col-4">
                                <div class="stat-number-large fw-bold" style="color: var(--volleyball-orange);">
                                    {{ $team->activeTournaments->count() }}
                                </div>
                                <div class="stat-label text-muted">Турниров</div>
                            </div>
                            <div class="col-4">
                                <div class="stat-number-large fw-bold" style="color: var(--volleyball-orange);">
                                    {{ $team->tournamentApplications->count() }}
                                </div>
                                <div class="stat-label text-muted">Заявок</div>
                            </div>
                            <div class="col-4">
                                <div class="stat-number-large fw-bold" style="color: var(--volleyball-orange);">
                                    ?
                                </div>
                                <div class="stat-label text-muted">Игроков</div>
                            </div>
                        </div>

                        <!-- Капитан -->
                        @if($team->captain)
                            <div class="captain-info mb-4">
                                <h6 class="fw-bold mb-3" style="color: var(--volleyball-blue);">
                                    <i class="fas fa-crown me-2"></i>Капитан
                                </h6>
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="user-avatar me-3">
                                        @if($team->captain->profile_photo_path)
                                            <div class="photo-modal-trigger player-avatar"
                                                 data-photo="{{ asset('storage/' . $team->captain->profile_photo_path) }}"
                                                 data-name="{{ $team->captain->name }}"
                                                 data-profile-url="{{ route('users.show', $team->captain) }}"
                                                 title="Посмотреть фото">
                                                <img src="{{ asset('storage/' . $team->captain->profile_photo_path) }}"
                                                     alt="{{ $team->captain->name }}"
                                                     class="w-100 h-100 player-avatar">
                                            </div>
                                        @else
{{--                                        @if($team->captain->profile_photo_path)--}}
{{--                                            <img src="{{ asset('storage/' . $team->captain->profile_photo_path) }}"--}}
{{--                                                 alt="{{ $team->captain->name }}"--}}
{{--                                                 class="player-avatar">--}}
{{--                                        @else--}}
                                            <div class="player-avatar bg-volleyball-blue text-white d-flex align-items-center justify-content-center">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-start">
                                        <div class="fw-bold">{{ $team->captain->name }}</div>
                                        <small class="text-muted">{{ $team->captain->email }}</small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Дата создания -->
                        <div class="creation-date">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                Создана: {{ $team->created_at->format('d.m.Y') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Правая колонка - турниры и состав -->
            <div class="col-lg-8">
                <!-- Активные турниры -->
                @if($team->activeTournaments->count() > 0)
                    <div class="card card-volleyball mb-4">
                        <div class="card-header bg-transparent border-bottom-0">
                            <h3 class="fw-bold mb-0" style="color: var(--volleyball-blue);">
                                <i class="fas fa-trophy me-2"></i>Активные турниры
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach($team->activeTournaments as $tournament)
                                    <div class="col-md-6">
                                        <div class="tournament-card card border h-100">
                                            <div class="card-body">
                                                <h6 class="fw-bold mb-2">{{ $tournament->name }}</h6>
                                                <div class="tournament-meta">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        {{ $tournament->start_date->format('d.m.Y') }} - {{ $tournament->end_date->format('d.m.Y') }}
                                                    </small>
                                                </div>
                                                <div class="mt-3">
                                                    <span class="badge bg-success me-2">Участвует</span>
                                                    <a href="{{ route('tournaments.teams.roster', ['tournament' => $tournament, 'team' => $team]) }}"
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-users me-1"></i>Состав
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- История заявок -->
                @if($team->tournamentApplications->count() > 0)
                    <div class="card card-volleyball">
                        <div class="card-header bg-transparent border-bottom-0">
                            <h3 class="fw-bold mb-0" style="color: var(--volleyball-blue);">
                                <i class="fas fa-history me-2"></i>История заявок
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                    <tr>
                                        <th>Турнир</th>
                                        <th>Статус</th>
                                        <th>Дата подачи</th>
                                        <th>Игроков в заявке</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($team->tournamentApplications as $application)
                                        <tr>
                                            <td>
                                                <strong>{{ $application->tournament->name ?? 'Неизвестный турнир' }}</strong>
                                            </td>
                                            <td>
                                                @if($application->status === 'approved')
                                                    <span class="badge bg-success">Принята</span>
                                                @elseif($application->status === 'rejected')
                                                    <span class="badge bg-danger">Отклонена</span>
                                                @elseif($application->status === 'pending')
                                                    <span class="badge bg-warning">На рассмотрении</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $application->status }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $application->created_at->format('d.m.Y H:i') }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-info">{{ $application->roster->count() }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card card-volleyball">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">Нет заявок на турниры</h4>
                            <p class="text-muted">Команда пока не подавала заявки на участие в турнирах</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .team-logo-large {
            width: 120px;
            height: 120px;
            background: var(--volleyball-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3em;
            margin: 0 auto;
        }

        .stat-number-large {
            font-size: 2rem;
        }

        .player-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            //border: 3px solid var(--volleyball-orange);
        }

        .user-avatar {
            width: 50px;
            height: 50px;
        }

        .tournament-card {
            transition: all 0.2s ease;
        }

        .tournament-card:hover {
            border-color: var(--volleyball-orange);
        }

        .bg-volleyball-blue {
            background: var(--volleyball-blue);
        }
    </style>
</x-app-layout>
