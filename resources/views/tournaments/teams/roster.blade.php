<x-app-layout>
    <div class="container py-4">
        <!-- Хлебные крошки -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Главная</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tournaments.index') }}" class="text-decoration-none">Турниры</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tournaments.show', $tournament) }}" class="text-decoration-none">{{ $tournament->name }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tournaments.teams', $tournament) }}" class="text-decoration-none">Команды</a></li>
                <li class="breadcrumb-item"><a href="{{ route('teams.show', $team) }}" class="text-decoration-none">{{ $team->name }}</a></li>
                <li class="breadcrumb-item active">Состав</li>
            </ol>
        </nav>

        <!-- Заголовок -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-volleyball">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="display-6 fw-bold mb-2" style="color: var(--volleyball-blue);">
                                    <i class="fas fa-list-alt me-3"></i>Состав команды
                                </h1>
                                <h2 class="h4 text-muted mb-1">{{ $team->name }} в турнире {{ $tournament->name }}</h2>
                                <div class="d-flex gap-4 text-muted">
                                    <span>
                                        <i class="fas fa-users me-1"></i>
                                        {{ $roster->count() }} игроков в заявке
                                    </span>
                                    <span>
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        Заявка подана: {{ $application->created_at->format('d.m.Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Состав команды -->
        <div class="row">
            @foreach($groupedRoster as $jerseyNumber => $players)
                <div class="col-lg-6 mb-4">
                    <div class="card card-volleyball h-100">
                        <div class="card-header bg-transparent border-bottom-0">
                            <h4 class="fw-bold mb-0" style="color: var(--volleyball-blue);">
{{--                                <i class="fas fa-{{ $role === 'captain' ? 'crown' : ($role === 'coach' ? 'whistle' : 'user') }} me-2"></i>--}}
                                #{{ $jerseyNumber }}
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach($players as $rosterEntry)
                                    <div class="col-12">
                                        <div class="player-card card border">
                                            <div class="card-body py-3">
                                                <div class="d-flex align-items-center">
                                                    <!-- Аватар -->
                                                    <div class="user-avatar me-3">
                                                        @if($rosterEntry->user->profile_photo_path)
                                                            <img src="{{ asset('storage/' . $rosterEntry->user->profile_photo_path) }}"
                                                                 alt="{{ $rosterEntry->user->name }}"
                                                                 class="player-avatar">
                                                        @else
                                                            <div class="player-avatar bg-volleyball-blue text-white d-flex align-items-center justify-content-center">
                                                                <i class="fas fa-user"></i>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Информация -->
                                                    <div class="flex-grow-1">
                                                        <h6 class="fw-bold mb-1">{{ $rosterEntry->user->name }}</h6>
                                                        <div class="player-meta">
                                                            @if($rosterEntry->position)
                                                                <span class="badge bg-secondary me-2">{{ $rosterEntry::POSITIONS[$rosterEntry->position] }}</span>
                                                            @endif
                                                            @if($rosterEntry->jersey_number)
                                                                <span class="badge bg-info me-2">#{{ $rosterEntry->jersey_number }}</span>
                                                            @endif
                                                            <small class="text-muted">
                                                                <i class="fas fa-envelope me-1"></i>
                                                                {{ $rosterEntry->user->email }}
                                                            </small>
                                                        </div>
                                                    </div>

                                                    <!-- Действия -->
                                                    <div class="ms-3">
                                                        <a href="{{ route('users.show', $rosterEntry->user) }}"
                                                           class="btn btn-sm btn-outline-primary"
                                                           title="Профиль игрока">
                                                            <i class="fas fa-external-link-alt"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Если состав пустой -->
        @if($roster->count() === 0)
            <div class="row">
                <div class="col-12">
                    <div class="card card-volleyball">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">Состав команды пуст</h4>
                            <p class="text-muted">В заявке на турнир пока нет игроков</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Детальная таблица -->
        @if($roster->count() > 0)
            <div class="card card-volleyball mt-4">
                <div class="card-header bg-transparent border-bottom-0">
                    <h4 class="fw-bold mb-0" style="color: var(--volleyball-blue);">
                        <i class="fas fa-table me-2"></i>Детальная информация о составе
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                            <tr>
                                <th>Игрок</th>
                                <th>Позиция</th>
                                <th>Номер</th>
                                <th>Email</th>
                                <th>Дата регистрации</th>
                                <th>Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($roster->sortBy('jersey_number') as $rosterEntry)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-3">
                                                @if($rosterEntry->user->profile_photo_path)
                                                    <img src="{{ asset('storage/' . $rosterEntry->user->profile_photo_path) }}"
                                                         alt="{{ $rosterEntry->user->name }}"
                                                         class="player-avatar-sm">
                                                @else
                                                    <div class="player-avatar-sm bg-secondary text-white d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $rosterEntry->user->name }}</div>
                                                @if($rosterEntry->user->email_verified_at)
                                                    <small class="text-success">
                                                        <i class="fas fa-check-circle me-1"></i>Подтвержден
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($rosterEntry->position)
                                            <span class="badge bg-secondary">{{ $rosterEntry::POSITIONS[$rosterEntry->position] }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($rosterEntry->jersey_number)
                                            <span class="badge bg-dark">#{{ $rosterEntry->jersey_number }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $rosterEntry->user->email }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $rosterEntry->user->created_at->format('d.m.Y') }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('users.show', $rosterEntry->user) }}"
                                           class="btn btn-sm btn-volleyball">
                                            <i class="fas fa-eye me-1"></i>Профиль
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <style>
        .player-card {
            transition: all 0.2s ease;
        }

        .player-card:hover {
            border-color: var(--volleyball-orange);
            transform: translateX(5px);
        }

        .player-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            //border: 3px solid var(--volleyball-orange);
        }

        .player-avatar-sm {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            //border: 2px solid var(--volleyball-orange);
        }

        .user-avatar {
            width: 50px;
            height: 50px;
        }

        .bg-volleyball-blue {
            background: var(--volleyball-blue);
        }

        .player-meta {
            font-size: 0.875rem;
        }
    </style>
</x-app-layout>
