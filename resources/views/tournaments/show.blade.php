<x-app-layout>
    <div class="container py-4">
        <!-- Хлебные крошки -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Главная</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tournaments.index') }}" class="text-decoration-none">Турниры</a></li>
                <li class="breadcrumb-item active">{{ $tournament->name }}</li>
            </ol>
        </nav>

        <!-- Заголовок турнира -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-volleyball">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="display-6 fw-bold" style="color: var(--volleyball-blue);">
                                    <i class="fas fa-trophy me-3"></i>{{ $tournament->name }}
                                </h1>
                                <p class="lead mb-2">{{ $tournament->description }}</p>
                                <div class="d-flex gap-4 text-muted">
                                    <span>
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        {{ $tournament->start_date->format('d.m.Y') }} - {{ $tournament->end_date->format('d.m.Y') }}
                                    </span>
                                    <span>
                                        <i class="fas fa-layer-group me-1"></i>
                                        Этапов: {{ $tournament->stages->count() }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="score-badge d-inline-block">
                                    <i class="fas fa-volleyball-ball me-2"></i>{{ $tournament::STATUS[$tournament->status] }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Этапы турнира -->
        <div class="row">
            <div class="col-12">
                <h3 class="fw-bold mb-4" style="color: var(--volleyball-blue);">
                    <i class="fas fa-layer-group me-2"></i>Этапы турнира
                </h3>

                <div class="row g-4">
                    @foreach($tournament->stages as $stage)
                        <div class="col-lg-6">
                            <div class="card card-volleyball h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="card-title fw-bold">{{ $stage->name }}</h5>
                                            <span class="badge bg-secondary">Этап {{ $stage->order }}</span>
                                        </div>
                                        <i class="fas fa-flag net-icon"></i>
                                    </div>

                                    <p class="text-muted mb-3">
                                        Групп: {{ $stage->groups->count() }}
                                    </p>

                                    <a href="{{ route('stages.show', ['tournament' => $tournament, 'stage' => $stage]) }}"
                                       class="btn btn-volleyball">
                                        <i class="fas fa-table me-2"></i>Таблица групп
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
