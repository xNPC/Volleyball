<!-- Модальное окно для просмотра фото -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold" id="photoModalLabel" style="color: var(--volleyball-blue);">
                    <i class="fas fa-user me-2"></i>
                    <span id="playerName">Игрок</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0 position-relative">
                <div id="photoLoading" class="d-none position-absolute top-50 start-50 translate-middle">
                    <div class="spinner-border text-volleyball-orange" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                </div>
                <img id="modalPhoto" src="" alt="" class="img-fluid rounded"
                     style="max-height: 70vh; object-fit: contain;"
                     onload="document.getElementById('photoLoading').classList.add('d-none')"
                     onerror="document.getElementById('photoLoading').classList.add('d-none')">
            </div>
            <div class="modal-footer border-top-0 justify-content-center">
{{--                <button type="button" class="btn btn-volleyball" data-bs-dismiss="modal">--}}
{{--                    <i class="fas fa-times me-2"></i>Закрыть--}}
{{--                </button>--}}
{{--                <a id="profileLink" href="#" class="btn btn-outline-primary">--}}
{{--                    <i class="fas fa-external-link-alt me-2"></i>Профиль игрока--}}
{{--                </a>--}}
            </div>
        </div>
    </div>
</div>

<!-- Стили для модального окна -->
<style>
    .text-volleyball-orange {
        color: var(--volleyball-orange) !important;
    }

    .photo-modal-trigger {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .photo-modal-trigger:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
    }

    .modal {
        border-radius: 0;
    }

    .modal-content {
        //border: 3px solid var(--volleyball-orange);
        //border-radius: 15px;
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, var(--volleyball-blue) 0%, var(--volleyball-orange) 100%);
        color: white;
    }

    .modal-header .btn-close {
        filter: invert(1);
    }

    #photoModalLabel {
        color: white !important;
    }
</style>

<!-- JavaScript для работы модального окна -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const photoModal = new bootstrap.Modal(document.getElementById('photoModal'));

        // Обработчик для всех элементов с классом photo-modal-trigger
        document.querySelectorAll('.photo-modal-trigger').forEach(trigger => {
            trigger.addEventListener('click', function() {
                const photoUrl = this.getAttribute('data-photo');
                const playerName = this.getAttribute('data-name');
                const profileUrl = this.getAttribute('data-profile-url');

                // Показываем индикатор загрузки
                document.getElementById('photoLoading').classList.remove('d-none');
                document.getElementById('modalPhoto').classList.add('d-none');

                // Устанавливаем данные в модальное окно
                document.getElementById('modalPhoto').src = photoUrl;
                document.getElementById('modalPhoto').alt = `Фото ${playerName}`;
                document.getElementById('playerName').textContent = playerName;

                // if (profileUrl) {
                //     document.getElementById('profileLink').href = profileUrl;
                //     document.getElementById('profileLink').style.display = 'inline-block';
                // } else {
                //     document.getElementById('profileLink').style.display = 'none';
                // }

                // Когда изображение загрузится
                document.getElementById('modalPhoto').onload = function() {
                    document.getElementById('photoLoading').classList.add('d-none');
                    document.getElementById('modalPhoto').classList.remove('d-none');
                };

                // Показываем модальное окно
                photoModal.show();
            });
        });

        // Закрытие модального окна по ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                photoModal.hide();
            }
        });

        // Предзагрузка изображения при наведении
        document.querySelectorAll('.photo-modal-trigger').forEach(trigger => {
            trigger.addEventListener('mouseenter', function() {
                const photoUrl = this.getAttribute('data-photo');
                const img = new Image();
                img.src = photoUrl;
            });
        });
    });
</script>
