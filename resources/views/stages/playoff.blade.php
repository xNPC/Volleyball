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
                                    <i class="fas fa-trophy me-3"></i>{{ $stage->name }}
                                </h1>
                                <p class="lead mb-0">Плейофф турнира: {{ $tournament->name }}</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="score-badge">Групп: {{ $groupsWithBrackets->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Табы групп -->
        @if($groupsWithBrackets->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="card card-volleyball">
                        <div class="card-header bg-transparent border-bottom-0 p-0">
                            <ul class="nav nav-tabs" id="groupsTab" role="tablist">
                                @foreach($groupsWithBrackets as $index => $group)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                                id="group-{{ $group->id }}-tab"
                                                data-bs-toggle="tab"
                                                data-bs-target="#group-{{ $group->id }}"
                                                type="button"
                                                role="tab">
                                            <i class="fas fa-users me-2"></i>{{ $group->name }}
                                            <span class="badge bg-secondary ms-2">{{ $group->teams->count() }} команд</span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="groupsTabContent">
                                @foreach($groupsWithBrackets as $index => $group)
                                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                                         id="group-{{ $group->id }}"
                                         role="tabpanel">

                                        <!-- Информация о группе -->
                                        <div class="mb-4">
                                            <h5 class="fw-bold" style="color: var(--volleyball-orange);">
                                                <i class="fas fa-info-circle me-2"></i>Сетка плейофф группы {{ $group->name }}
                                            </h5>
                                            <p class="text-muted">Участников: {{ $group->teams->count() }}</p>
                                        </div>

                                        <!-- Сетка плейофф -->
                                        @include('components.playoff-bracket-flexible', [
                                            'bracket' => $group->bracket ?? [],
                                            'group' => $group
                                        ])

                                        <!-- Список команд группы -->
                                        <div class="mt-4">
                                            <h6>Команды в группе:</h6>
                                            <div class="row">
                                                @foreach($group->teams as $team)
                                                    <div class="col-md-3 mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-volleyball-ball me-2" style="color: var(--volleyball-orange);"></i>
                                                            <span>{{ $team->team->name }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
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
</x-app-layout>
