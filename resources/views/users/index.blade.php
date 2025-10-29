<x-app-layout>
    <div class="container py-4">
        <!-- Хлебные крошки -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Главная</a></li>
                <li class="breadcrumb-item active">Игроки</li>
            </ol>
        </nav>

        <!-- Заголовок -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-5 fw-bold text-center mb-3" style="color: var(--volleyball-blue);">
                    <i class="fas fa-users me-3"></i>Игроки
                </h1>
                <p class="text-center text-muted">Список всех зарегистрированных игроков</p>
            </div>
        </div>

        <!-- Статистика -->
{{--        <div class="row mb-4">--}}
{{--            <div class="col-md-3 col-6">--}}
{{--                <div class="card card-volleyball text-center">--}}
{{--                    <div class="card-body">--}}
{{--                        <h3 class="fw-bold" style="color: var(--volleyball-orange);">{{ $stats['total_users'] }}</h3>--}}
{{--                        <p class="mb-0 text-muted">Всего игроков</p>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-md-3 col-6">--}}
{{--                <div class="card card-volleyball text-center">--}}
{{--                    <div class="card-body">--}}
{{--                        <h3 class="fw-bold" style="color: var(--volleyball-orange);">--}}
{{--                            {{ $stats['new_users_month'] }}--}}
{{--                        </h3>--}}
{{--                        <p class="mb-0 text-muted">Новых за месяц</p>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-md-3 col-6">--}}
{{--                <div class="card card-volleyball text-center">--}}
{{--                    <div class="card-body">--}}
{{--                        <h3 class="fw-bold" style="color: var(--volleyball-orange);">--}}
{{--                            {{ $stats['users_with_applications'] }}--}}
{{--                        </h3>--}}
{{--                        <p class="mb-0 text-muted">С принятыми заявками</p>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-md-3 col-6">--}}
{{--                <div class="card card-volleyball text-center">--}}
{{--                    <div class="card-body">--}}
{{--                        <h3 class="fw-bold" style="color: var(--volleyball-orange);">--}}
{{--                            {{ $stats['verified_users'] }}--}}
{{--                        </h3>--}}
{{--                        <p class="mb-0 text-muted">Подтвержденных</p>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

        <!-- Поиск и фильтры -->
        <div class="card card-volleyball mb-4">
            <div class="card-body">
                <form action="{{ route('users.index') }}" method="GET">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text"
                                       name="search"
                                       class="form-control border-start-0"
                                       placeholder="Поиск по имени или email..."
                                       value="{{ $search }}">
                                @if($search)
                                    <a href="{{ route('users.index') }}" class="input-group-text bg-transparent border-start-0 text-muted" title="Очистить поиск">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
{{--                        <div class="col-md-4 mb-3 mb-md-0">--}}
{{--                            <select name="filter" class="form-select" onchange="this.form.submit()">--}}
{{--                                <option value="all" {{ $filter == 'all' ? 'selected' : '' }}>Все игроки</option>--}}
{{--                                <option value="with_teams" {{ $filter == 'with_teams' ? 'selected' : '' }}>С командами</option>--}}
{{--                                <option value="new" {{ $filter == 'new' ? 'selected' : '' }}>Новые</option>--}}
{{--                                <option value="verified" {{ $filter == 'verified' ? 'selected' : '' }}>Подтвержденные</option>--}}
{{--                            </select>--}}
{{--                        </div>--}}
                        <div class="col-md-2 text-end">
                            <button type="submit" class="btn btn-volleyball w-100">
                                <i class="fas fa-filter me-1"></i>Применить
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
                            Найдено {{ $users->total() }} игроков по запросу "{{ $search }}" с фильтром "{{ $filter }}"
                        @elseif($search)
                            Найдено {{ $users->total() }} игроков по запросу "{{ $search }}"
                        @else
                            Показаны игроки с фильтром "{{ $filter }}": {{ $users->total() }} результатов
                        @endif
                    </div>
                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Сбросить
                    </a>
                </div>
            </div>
        @endif

        <!-- Список пользователей -->
        <div class="row g-4">
            @forelse($users as $user)
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="card card-volleyball user-card h-100">
                        <div class="card-body text-center">
                            <!-- Аватар -->
                            <div class="user-avatar mx-auto mb-3">
                                @if($user->profile_photo_path)
                                    <div class="photo-modal-trigger player-avatar"
                                         data-photo="{{ asset('storage/' . $user->profile_photo_path) }}"
                                         data-name="{{ $user->name }}"
                                         data-profile-url="{{ route('users.show', $user) }}"
                                         title="Посмотреть фото">
                                        <img src="{{ asset('storage/' . $user->profile_photo_path) }}"
                                             alt="{{ $user->name }}"
                                             class="w-100 h-100 player-avatar">
                                    </div>
                                @else
{{--                                @if($user->profile_photo_path)--}}
{{--                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}"--}}
{{--                                         alt="{{ $user->name }}"--}}
{{--                                         class="player-avatar photo-modal-trigger">--}}
{{--                                @else--}}
                                    <div class="player-avatar bg-volleyball-blue text-white d-flex align-items-center justify-content-center mx-auto">
                                        <i class="fas fa-user fa-lg"></i>
                                    </div>
                                @endif
                            </div>

                            <!-- Информация -->
                            <h5 class="card-title fw-bold mb-2" style="color: var(--volleyball-blue);">
                                {{ $user->name }}
                            </h5>

                            <p class="text-muted mb-3">
                                @if($user->email_verified_at)
                                    <i class="fas fa-check-circle text-success me-1" title="Подтвержден"></i>
                                @else
                                    <i class="fas fa-clock text-warning me-1" title="Не подтвержден"></i>
                                @endif
                                Участник с {{ $user->created_at->format('d.m.Y') }}
                            </p>

                            <!-- Статистика -->
                            <div class="user-stats mb-3">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="stat-number fw-bold" style="color: var(--volleyball-orange);">
                                            {{ $user->approved_applications_count }}
                                        </div>
                                        <div class="stat-label small text-muted">Команд</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-number fw-bold" style="color: var(--volleyball-orange);">
                                            {{ $user->total_applications_count }}
                                        </div>
                                        <div class="stat-label small text-muted">Турниров</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Кнопка -->
                            <a href="{{ route('users.show', $user) }}"
                               class="btn btn-volleyball btn-sm">
                                <i class="fas fa-eye me-1"></i>Профиль
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
                                Игроки не найдены
                            @else
                                Игроки не найдены
                            @endif
                        </h4>
                        <p class="text-muted">
                            @if($search || $filter != 'all')
                                Попробуйте изменить параметры поиска или сбросить фильтры
                            @else
                                Здесь появятся игроки, когда они зарегистрируются
                            @endif
                        </p>
                        @if($search || $filter != 'all')
                            <a href="{{ route('users.index') }}" class="btn btn-volleyball">
                                <i class="fas fa-redo me-1"></i>Показать всех игроков
                            </a>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Пагинация -->
        @if($users->hasPages())
            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex justify-content-center">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <style>
        .user-avatar {
            width: 100px;
            height: 100px;
        }

        .user-card {
            transition: all 0.3s ease;
        }

        .user-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 1.5rem;
        }

        .player-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            //border: 3px solid var(--volleyball-orange);
        }

        .bg-volleyball-blue {
            background: var(--volleyball-blue);
        }

        .input-group-text {
            transition: all 0.3s ease;
        }

        .input-group:focus-within .input-group-text {
            color: var(--volleyball-orange);
        }
    </style>

    <script>
        // Авто-сабмит формы при очистке поиска
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="search"]');
            const clearButton = document.querySelector('.input-group-text .fa-times');

            if (clearButton) {
                clearButton.closest('a').addEventListener('click', function(e) {
                    e.preventDefault();
                    searchInput.value = '';
                    searchInput.closest('form').submit();
                });
            }

            // Авто-сабмит при нажатии Enter в поле поиска
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    this.closest('form').submit();
                }
            });
        });
    </script>
</x-app-layout>
