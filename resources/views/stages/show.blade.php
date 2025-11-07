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

        <!-- Табы групп -->
        @if($stage->groups->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="card card-volleyball">
                        <div class="card-header bg-transparent border-bottom-0 p-0">
                            <ul class="nav nav-tabs" id="groupsTab" role="tablist">
                                @foreach($groupsWithStandings as $index => $group)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                                id="group-{{ $group->id }}-tab"
                                                data-bs-toggle="tab"
                                                data-bs-target="#group-{{ $group->id }}"
                                                type="button"
                                                role="tab">
                                            <i class="fas fa-users me-2"></i>{{ $group->name }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="groupsTabContent">
                                @foreach($groupsWithStandings as $index => $group)
                                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                                         id="group-{{ $group->id }}"
                                         role="tabpanel">

                                        <!-- Внутренние табы для группы -->
                                        <ul class="nav nav-pills mb-4" id="group-{{ $group->id }}-subtab" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active"
                                                        id="table-{{ $group->id }}-tab"
                                                        data-bs-toggle="pill"
                                                        data-bs-target="#table-{{ $group->id }}"
                                                        type="button">
                                                    <i class="fas fa-table me-2"></i>Турнирная таблица
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link"
                                                        id="games-{{ $group->id }}-tab"
                                                        data-bs-toggle="pill"
                                                        data-bs-target="#games-{{ $group->id }}"
                                                        type="button">
                                                    <i class="fas fa-volleyball-ball me-2"></i>Игры группы
                                                </button>
                                            </li>
                                        </ul>

                                        <div class="tab-content">
                                            <!-- Турнирная таблица -->
                                            <div class="tab-pane fade show active" id="table-{{ $group->id }}">
                                                @include('partials.group-table', ['group' => $group])
                                            </div>

                                            <!-- Игры группы -->
                                            <div class="tab-pane fade" id="games-{{ $group->id }}">
                                                @include('partials.group-games', ['group' => $group])
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>В этом этапе пока нет групп.
            </div>
        @endif
    </div>

    <style>
        .nav-tabs .nav-link {
            color: var(--volleyball-blue);
            font-weight: 600;
            border: none;
            border-radius: 0;
            padding: 12px 20px;
        }

        .nav-tabs .nav-link.active {
            background: var(--volleyball-orange);
            color: white;
            border: none;
        }

        .nav-pills .nav-link {
            color: var(--volleyball-blue);
            font-weight: 500;
            margin-right: 10px;
        }

        .nav-pills .nav-link.active {
            background: var(--volleyball-blue);
            color: white;
        }

        .tournament-table {
            font-size: 0.85rem;
        }

        .tournament-table th {
            background: var(--volleyball-blue);
            color: white;
            text-align: center;
            vertical-align: middle;
            font-weight: 600;
            padding: 8px 4px;
        }

        .tournament-table td {
            padding: 6px 4px;
            vertical-align: middle;
            text-align: center;
        }

        .team-name {
            text-align: left !important;
            font-weight: 600;
        }

        .result-cell {
            background: #f8f9fa;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .result-cell:hover {
            background: #e9ecef;
        }

        .win-score {
            color: #28a745;
            font-weight: bold;
        }

        .lose-score {
            color: #dc3545;
        }

        .current-team {
            background: rgba(255, 107, 53, 0.1);
            font-weight: bold;
        }

        .qualification-zone {
            background: rgba(40, 167, 69, 0.1);
        }

        .relegation-zone {
            background: rgba(220, 53, 69, 0.1);
        }
    </style>
</x-app-layout>
