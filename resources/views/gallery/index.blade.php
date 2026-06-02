<x-app-layout>
    <div class="container py-4">
        <!-- Хлебные крошки -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Главная</a></li>
                <li class="breadcrumb-item active">Фотогалерея</li>
            </ol>
        </nav>

        <!-- Заголовок -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-volleyball">
                    <div class="card-body">
                        <h1 class="display-6 fw-bold" style="color: var(--volleyball-blue);">
                            <i class="fas fa-camera me-3"></i>Фотогалерея
                        </h1>
                        <p class="lead mb-0">Альбомы с фотографиями</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Список альбомов -->
        <div class="row g-4">
            @forelse($albums as $album)
                <div class="col-md-6 col-lg-4">
                    <div class="card card-volleyball h-100">
                        <div class="position-relative">
                            <img src="{{ $album->cover_url ?? '/images/placeholder.jpg' }}"
                                 alt="{{ $album->title }}"
                                 class="card-img-top"
                                 style="height: 250px; object-fit: cover; border-radius: 0;">
                            <div class="position-absolute top-0 end-0 m-3">
                                <span class="badge bg-primary">
                                    <i class="fas fa-image me-1"></i>{{ $album->photos_count }} фото
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold">
                                <a href="{{ route('gallery.show', $album->slug) }}" class="text-decoration-none" style="color: var(--volleyball-blue);">
                                    {{ $album->title }}
                                </a>
                            </h5>
                            <p class="card-text text-muted">{{ Str::limit($album->description, 100) }}</p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 pb-3">
                            <a href="{{ route('gallery.show', $album->slug) }}" class="btn btn-volleyball w-100">
                                <i class="fas fa-eye me-2"></i>Смотреть фото
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>Альбомов пока нет
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
