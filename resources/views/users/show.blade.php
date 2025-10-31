<x-app-layout>
    <div class="container py-4">
        <!-- Хлебные крошки -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Главная</a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-decoration-none">Игроки</a></li>
                <li class="breadcrumb-item active">{{ $user->name }}</li>
            </ol>
        </nav>

        <!-- Профиль пользователя -->
        <div class="row">
            <!-- Левая колонка - информация -->
            <div class="col-lg-4 mb-4">
                <div class="card card-volleyball">
                    <div class="card-body text-center">
                        <!-- Аватар -->
                        <div class="user-avatar mx-auto mb-4">
{{--                            @if($user->profile_photo_path)--}}
{{--                                <div class="photo-modal-trigger player-avatar-large"--}}
{{--                                     data-photo="{{ asset('storage/' . $user->profile_photo_path) }}"--}}
{{--                                     data-name="{{ $user->name }}"--}}
{{--                                     data-profile-url="{{ route('users.show', $user) }}"--}}
{{--                                     title="Посмотреть фото">--}}
{{--                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}"--}}
{{--                                         alt="{{ $user->name }}"--}}
{{--                                         class="w-100 h-100 player-avatar-large">--}}
{{--                                </div>--}}
{{--                            @else--}}
                            @if($user->profile_photo_path)
                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}"
                                     alt="{{ $user->name }}"
                                     class="player-avatar-large">
                            @else
                                <div class="player-avatar-large bg-volleyball-blue text-white d-flex align-items-center justify-content-center mx-auto">
                                    <i class="fas fa-user fa-2x"></i>
                                </div>
                            @endif
                        </div>

                        <!-- Основная информация -->
                        <h1 class="h3 fw-bold mb-2" style="color: var(--volleyball-blue);">
                            {{ $user->name }}
                        </h1>

{{--                        <p class="text-muted mb-3">--}}
{{--                            @if($user->email_verified_at)--}}
{{--                                <i class="fas fa-check-circle text-success me-1" title="Подтвержден"></i>--}}
{{--                                Подтвержденный аккаунт--}}
{{--                            @else--}}
{{--                                <i class="fas fa-clock text-warning me-1" title="Не подтвержден"></i>--}}
{{--                                Ожидает подтверждения--}}
{{--                            @endif--}}
{{--                        </p>--}}

                        <!-- Статистика -->
                        <div class="row text-center mb-4">
{{--                            <div class="col-4">--}}
{{--                                <div class="stat-number-large fw-bold" style="color: var(--volleyball-orange);">--}}
{{--                                    {{ $user->approvedTournamentApplications()->count() }}--}}
{{--                                </div>--}}
{{--                                <div class="stat-label text-muted">Заявок</div>--}}
{{--                            </div>--}}
{{--                            <div class="col-4">--}}
{{--                                <div class="stat-number-large fw-bold" style="color: var(--volleyball-orange);">--}}
{{--                                    {{ $user->applicationRosters()->count() }}--}}
{{--                                </div>--}}
{{--                                <div class="stat-label text-muted">Участий</div>--}}
{{--                            </div>--}}
{{--                            <div class="col-4">--}}
{{--                                <div class="stat-number-large fw-bold" style="color: var(--volleyball-orange);">--}}
{{--                                    0--}}
{{--                                </div>--}}
{{--                                <div class="stat-label text-muted">Побед</div>--}}
{{--                            </div>--}}
                        </div>

                        <!-- Дополнительная информация -->
                        <div class="user-info text-start">
                            <div class="info-item mb-2">
                                <i class="fas fa-birthday-cake me-2 text-muted"></i>
                                <span class="text-muted">Дата рождения: {{ $user->birthday?->format('d.m.Y') }}</span>
                            </div>
                            <div class="info-item mb-2">
                                <i class="fas fa-envelope me-2 text-muted"></i>
                                <span class="text-muted">{{ $user->email }}</span>
                            </div>
                            <div class="info-item mb-2">
                                <i class="fas fa-calendar me-2 text-muted"></i>
                                <span class="text-muted">Зарегистрирован: {{ $user->created_at->format('d.m.Y') }}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-clock me-2 text-muted"></i>
                                <span class="text-muted">Последняя активность: {{ $user->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Правая колонка - заявки -->
            <div class="col-lg-8">
                <!-- Турнирные заявки пользователя -->
                <div class="card card-volleyball mb-4">
                    <div class="card-header bg-transparent border-bottom-0">
                        <h3 class="fw-bold mb-0" style="color: var(--volleyball-blue);">
                            <i class="fas fa-clipboard-list me-2"></i>Заявлен в командах
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($user->tournamentApplications->count() > 0)
                            <div class="row g-3">
                                @foreach($user->tournamentApplications as $application)
                                    @php
                                        $team = $application->team;
                                        $tournament = $application->tournament;
                                        // Получаем роль пользователя в этой заявке
                                        $userRoster = $user->applicationRosters
                                            ->where('application_id', $application->id)
                                            ->first();
                                    @endphp
                                    <div class="col-md-6">
                                        <div class="application-card card border h-100">
                                            <div class="card-body">
                                                <!-- Команда -->
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="team-logo-small me-3">
                                                        <i class="fas fa-volleyball-ball"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="fw-bold mb-1">
                                                            @if($team->name)
                                                                <a href="{{ route('teams.show', $team) }}" class="text-black text-decoration-none">{{ $team->name }}</a>
                                                            @endif
                                                        </h6>
                                                        @if($tournament)
                                                            <p class="mb-0 text-muted small">
                                                                <i class="fas fa-trophy me-1"></i>
                                                                <a href="{{ route('tournaments.show', $tournament) }}" class="text-decoration-none">{{ $tournament->name }}</a>
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Информация о заявке -->
                                                <div class="application-info">
                                                    <!-- Статус заявки -->
                                                    <div class="mb-2">
                                                        @if($application->status === 'approved')
                                                            <span class="badge bg-success">
                                                                <i class="fas fa-check me-1"></i>Принята
                                                            </span>
                                                        @elseif($application->status === 'rejected')
                                                            <span class="badge bg-danger">
                                                                <i class="fas fa-times me-1"></i>Отклонена
                                                            </span>
                                                        @elseif($application->status === 'pending')
                                                            <span class="badge bg-warning">
                                                                <i class="fas fa-clock me-1"></i>На рассмотрении
                                                            </span>
                                                        @endif
                                                        <small class="text-muted ms-2">
                                                            {{ $application->created_at->format('d.m.Y') }}
                                                        </small>
                                                    </div>

                                                    <!-- Роль пользователя -->
                                                    @if($userRoster)
                                                        <div class="mb-2">
                                                            <small class="text-muted">Позиция:</small>
                                                            @if($userRoster->position)
                                                                <span class="badge bg-secondary ms-1">
                                                                    {{ $userRoster::POSITIONS[$userRoster->position] }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-clipboard-list fa-2x text-muted mb-3"></i>
                                <p class="text-muted mb-0">Игрок пока не участвовал в заявках на турниры</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- История участий в заявках -->
                @if($user->applicationRosters->count() > 0)
                    <div class="card card-volleyball">
                        <div class="card-header bg-transparent border-bottom-0">
                            <h4 class="fw-bold mb-0" style="color: var(--volleyball-blue);">
                                <i class="fas fa-history me-2"></i>История участий в турнирах
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                    <tr>
                                        <th>Команда</th>
                                        <th>Турнир</th>
                                        <th>Позиция</th>
                                        <th>Дата</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($user->applicationRosters as $roster)
                                        @php
                                            $application = $roster->Application;
                                            $team = $application->team ?? null;
                                            $tournament = $application->tournament ?? null;
                                        @endphp
                                        <tr>
                                            <td>
                                                @if($team)
                                                    <a href="{{ route('teams.show', $team) }}" class="text-black text-decoration-none">{{ $team->name }}</a>
                                                @else
                                                    <span class="text-muted">Неизвестная команда</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($tournament)
                                                    <a href="{{ route('tournaments.show', $tournament) }}" class="text-black text-decoration-none">{{ $tournament->name }}</a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
{{--                                                <span class="badge bg-primary">--}}
{{--                                                    {{ $roster->role ?? 'Игрок' }}--}}
{{--                                                </span>--}}
                                                @if($roster->position)
                                                    <small class="text-muted ms-1">{{ $roster::POSITIONS[$roster->position] }}</small>
                                                @endif
                                            </td>
{{--                                            <td>--}}
{{--                                                @if($application)--}}
{{--                                                    @if($application->status === 'approved')--}}
{{--                                                        <span class="badge bg-success">Принята</span>--}}
{{--                                                    @elseif($application->status === 'rejected')--}}
{{--                                                        <span class="badge bg-danger">Отклонена</span>--}}
{{--                                                    @elseif($application->status === 'pending')--}}
{{--                                                        <span class="badge bg-warning">На рассмотрении</span>--}}
{{--                                                    @else--}}
{{--                                                        <span class="badge bg-secondary">{{ $application->status }}</span>--}}
{{--                                                    @endif--}}
{{--                                                @else--}}
{{--                                                    <span class="badge bg-secondary">Неизвестно</span>--}}
{{--                                                @endif--}}
{{--                                            </td>--}}
                                            <td>{{ $roster->created_at->format('d.m.Y') }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .player-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            //border: 4px solid var(--volleyball-orange);
        }

        .stat-number-large {
            font-size: 2rem;
        }

        .team-logo-small {
            width: 40px;
            height: 40px;
            background: var(--volleyball-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1em;
        }

        .application-card {
            transition: all 0.2s ease;
        }

        .application-card:hover {
            border-color: var(--volleyball-orange);
            transform: translateY(-2px);
        }

        .bg-volleyball-blue {
            background: var(--volleyball-blue);
        }
    </style>
</x-app-layout>
