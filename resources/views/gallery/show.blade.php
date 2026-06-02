<x-app-layout>
    <div class="container py-4">
        <!-- Хлебные крошки -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Главная</a></li>
                <li class="breadcrumb-item"><a href="{{ route('gallery.index') }}" class="text-decoration-none">Фотогалерея</a></li>
                <li class="breadcrumb-item active">{{ $album->title }}</li>
            </ol>
        </nav>

        <!-- Заголовок альбома -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-volleyball">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="display-6 fw-bold" style="color: var(--volleyball-blue);">
                                    <i class="fas fa-camera me-3"></i>{{ $album->title }}
                                </h1>
                                <p class="lead mb-0">{{ $album->description }}</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="score-badge d-inline-block">
                                    <i class="fas fa-image me-2"></i>{{ $album->photos->count() }} фото
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Галерея фото -->
        <div class="row g-4">
            @forelse($album->photos as $photo)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card card-volleyball h-100 cursor-pointer" onclick="openModal('{{ $photo->url }}')">
                        <img src="{{ $photo->thumbnail_url }}"
                             alt="{{ $photo->original_name }}"
                             class="card-img-top"
                             style="height: 200px; object-fit: cover; cursor: pointer; border-radius: 0;">
                        <!--<div class="card-body text-center p-2">
                            <small class="text-muted">{{ Str::limit($photo->original_name, 30) }}</small>
                        </div>-->
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>В этом альбоме пока нет фотографий
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Модальное окно для просмотра фото -->
    <div id="modal" class="fixed inset-0 bg-black hidden items-center justify-center z-50" onclick="closeModal()">
        <div class="relative max-w-7xl mx-auto p-4">
            <img id="modal-img" src="" alt="" class="max-w-full max-h-screen object-contain">
            <!--<button class="absolute top-4 right-4 text-white text-4xl hover:text-gray-300 z-50" onclick="closeModal()">&times;</button>-->
        </div>
    </div>

    <style>
        .fixed {
            position: fixed;
        }
        .inset-0 {
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
        }
        .bg-black {
            background-color: #000000 !important; /* Полностью черный фон */
            background: #000000 !important;
        }
        .hidden {
            display: none;
        }
        .flex {
            display: flex;
        }
        .items-center {
            align-items: center;
        }
        .justify-center {
            justify-content: center;
        }
        .z-50 {
            z-index: 9999; /* Увеличил z-index */
        }
        .max-w-7xl {
            max-width: 80rem;
        }
        .mx-auto {
            margin-left: auto;
            margin-right: auto;
        }
        .p-4 {
            padding: 1rem;
        }
        .max-w-full {
            max-width: 100%;
        }
        .max-h-screen {
            max-height: 100vh;
        }
        .object-contain {
            object-fit: contain;
        }
        .absolute {
            position: absolute;
        }
        .top-4 {
            top: 1rem;
        }
        .right-4 {
            right: 1rem;
        }
        .text-white {
            color: #fff;
        }
        .text-4xl {
            font-size: 2.5rem;
        }
        .hover\:text-gray-300:hover {
            color: #d1d5db;
        }
        .cursor-pointer {
            cursor: pointer;
        }

        /* Дополнительные стили для полного затемнения */
        body.modal-open {
            overflow: hidden;
        }

        #modal {
            background: rgba(0, 0, 0, 0.95); /* 95% черный, можно 100% */
        }
    </style>

    <script>
        function openModal(src) {
            var modal = document.getElementById('modal');
            var modalImg = document.getElementById('modal-img');

            modal.style.display = 'flex';
            modalImg.src = src;
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            var modal = document.getElementById('modal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });
    </script>
</x-app-layout>
