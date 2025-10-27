<x-app-layout>
    <div class="container py-4">
        <!-- Хлебные крошки -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Главная</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tournaments.index') }}" class="text-decoration-none">Турниры</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tournaments.show', $group->stage->tournament) }}" class="text-decoration-none">{{ $group->stage->tournament->name }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('stages.show', ['tournament' => $group->stage->tournament, 'stage' => $group->stage]) }}" class="text-decoration-none">{{ $group->stage->name }}</a></li>
                <li class="breadcrumb-item active">{{ $group->name }}</li>
            </ol>
        </nav>

        <!-- Заголовок группы -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-volleyball">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="display-6 fw-bold" style="color: var(--volleyball-blue);">
                                    <i class="fas fa-users me-3"></i>{{ $group->name }}
                                </h1>
                                <p class="lead mb-0">
                                    {{ $group->stage->name }} • {{ $group->stage->tournament->name }}
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="score-badge">{{ $group->teams->count() }} команд</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Детальная таблица команд -->
        <div class="row">
            <div class="col-12">
                <div class="card card-volleyball">
                    <div class="card-header bg-transparent">
                        <h3 class="fw-bold mb-0" style="color: var(--volleyball-orange);">
                            <i class="fas fa-list-ol me-2"></i>Турнирная таблица
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                <tr>
                                    <th width="60">#</th>
                                    <th>Команда</th>
                                    <th class="text-center">Игры</th>
                                    <th class="text-center">Победы</th>
                                    <th class="text-center">Поражения</th>
                                    <th class="text-center">Очки</th>
                                    <th class="text-center">Форма</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($group->teams as $index => $team)
                                    <tr class="@if($index < 2) table-success @elseif($index >= count($group->teams) - 2) table-danger @endif">
                                        <td class="fw-bold fs-5">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="team-logo me-3">
                                                    <i class="fas fa-volleyball-ball"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold fs-6">{{ $team->name }}</div>
                                                    <small class="text-muted">Капитан: Неизвестен</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center fw-semibold">0</td>
                                        <td class="text-center fw-semibold">0</td>
                                        <td class="text-center fw-semibold">0</td>
                                        <td class="text-center">
                                            <span class="badge bg-primary fs-6">{{ $team->points }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">-</span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Легенда -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card card-volleyball">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Легенда:</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <span class="badge bg-success me-2">Выход в плей-офф</span>
                            </div>
                            <div class="col-md-4">
                                <span class="badge bg-danger me-2">Выбывание</span>
                            </div>
                            <div class="col-md-4">
                                <span class="badge bg-secondary me-2">Нейтрально</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
