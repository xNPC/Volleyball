<x-app-layout>
    <div class="container py-4">
        <!-- Хлебные крошки -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Главная</a></li>
                <li class="breadcrumb-item active">Турниры</li>
            </ol>
        </nav>

        <!-- Заголовок -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-5 fw-bold text-center mb-3" style="color: var(--volleyball-blue);">
                    <i class="fas fa-trophy me-3"></i>Турниры
                </h1>
                <p class="text-center text-muted">Выберите турнир для просмотра деталей</p>
            </div>
        </div>

        <!-- Список турниров -->
        <div class="row g-4">
            @forelse($tournaments as $tournament)
                <div class="col-md-6 col-lg-4">
                    <div class="card card-volleyball tournament-card h-100">
                        <div class="card-body d-flex flex-column">
                            <!-- Заголовок карточки -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title fw-bold" style="color: var(--volleyball-blue);">
                                        {{ $tournament->name }}
                                    </h5>
                                    <span class="badge bg-primary">{{ $tournament->stages_count }} этапов</span>
                                </div>
                                <i class="fas fa-volleyball-ball net-icon"></i>
                            </div>

                            <!-- Описание -->
                            <p class="card-text flex-grow-1 text-muted">
                                {{ Str::limit($tournament->description, 120) }}
                            </p>

                            <!-- Даты -->
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    {{ $tournament->start_date->format('d.m.Y') }} - {{ $tournament->end_date->format('d.m.Y') }}
                                </small>
                            </div>

                            <!-- Кнопка -->
                            <a href="{{ route('tournaments.show', $tournament) }}"
                               class="btn btn-volleyball mt-auto">
                                <i class="fas fa-eye me-2"></i>Смотреть турнир
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Турниры не найдены</h4>
                        <p class="text-muted">Здесь появятся турниры, когда они будут созданы</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
