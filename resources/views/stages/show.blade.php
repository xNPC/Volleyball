<x-app-layout>
    <div class="container py-4">
        <!-- Хлебные крошки -->
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
                                    <i class="fas fa-layer-group me-3"></i>{{ $stage->name }}
                                </h1>
                                <p class="lead mb-0">Этап турнира: {{ $tournament->name }}</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="score-badge">Этап {{ $stage->order }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Группы этапа -->
        <div class="row">
            <div class="col-12">
                <h3 class="fw-bold mb-4" style="color: var(--volleyball-blue);">
                    <i class="fas fa-table me-2"></i>Группы этапа
                </h3>

                @foreach($stage->groups as $group)
                    <div class="card card-volleyball mb-4">
                        <div class="card-header bg-transparent border-bottom-0">
                            <h4 class="fw-bold mb-0" style="color: var(--volleyball-orange);">
                                <i class="fas fa-users me-2"></i>{{ $group->name }}
                            </h4>
                        </div>
                        <div class="card-body">
                            <!-- Таблица команд -->
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Команда</th>
                                        <th class="text-center">Очки</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($group->teams as $index => $team)
                                        <tr class="@if($index < 2) table-success @elseif($index >= count($group->teams) - 2) table-danger @endif">
                                            <td class="fw-bold">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="team-logo me-3">
                                                        <i class="fas fa-volleyball-ball"></i>
                                                    </div>
                                                    <span class="fw-semibold">{{ $team->name }}</span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary fs-6">{{ $team->points }}</span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('groups.show', $group) }}"
                                                   class="btn btn-sm btn-volleyball">
                                                    <i class="fas fa-eye me-1"></i>Подробнее
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
