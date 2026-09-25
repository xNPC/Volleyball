<style>
    .roster-photo-modal {
        position: fixed;
        inset: 0;
        z-index: 1080;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, 0.7);
    }

    .roster-photo-modal.open {
        display: flex;
    }

    .roster-photo-modal-card {
        max-width: min(90vw, 640px);
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }

    .roster-photo-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        background: #f8f9fa;
        font-weight: 600;
    }

    .roster-photo-modal-close {
        border: none;
        background: transparent;
        font-size: 22px;
        line-height: 1;
        color: #6c757d;
        cursor: pointer;
        padding: 0 4px;
    }

    .roster-photo-modal-close:hover {
        color: #343a40;
    }

    .roster-photo-modal-body {
        max-height: 70vh;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #000;
        position: relative;
        min-height: 120px;
    }

    .roster-photo-modal-body img {
        max-width: 100%;
        max-height: 70vh;
        display: block;
    }

    .roster-photo-modal-spinner {
        position: absolute;
        color: #fff;
    }

    .roster-photo-trigger {
        display: inline-block;
    }
</style>

<div class="roster-photo-modal" id="rosterPhotoModal" aria-hidden="true">
    <div class="roster-photo-modal-card" role="dialog" aria-modal="true">
        <div class="roster-photo-modal-header">
            <span class="roster-photo-modal-name" id="rosterPhotoName">Игрок</span>
            <button type="button" class="roster-photo-modal-close" id="rosterPhotoClose" aria-label="Закрыть">&times;</button>
        </div>
        <div class="roster-photo-modal-body">
            <span class="roster-photo-modal-spinner" id="rosterPhotoSpinner">Загрузка...</span>
            <img id="rosterPhotoImage" src="" alt="" style="display:none;">
        </div>
    </div>
</div>

<script>
    (function () {
        function getEls() {
            return {
                modal: document.getElementById('rosterPhotoModal'),
                image: document.getElementById('rosterPhotoImage'),
                nameEl: document.getElementById('rosterPhotoName'),
                spinner: document.getElementById('rosterPhotoSpinner'),
            };
        }

        function open(event, trigger) {
            var els = getEls();
            if (!els.modal || !els.image) {
                return;
            }

            var playerName = trigger.getAttribute('data-name') || 'Игрок';
            var photoUrl = trigger.getAttribute('data-photo');

            els.image.style.display = 'none';
            els.spinner.style.display = '';
            els.nameEl.textContent = playerName;
            els.image.alt = 'Фото ' + playerName;
            els.image.onload = function () {
                els.spinner.style.display = 'none';
                els.image.style.display = '';
            };
            els.image.onerror = function () {
                els.spinner.style.display = 'none';
                els.image.style.display = '';
            };
            els.image.src = photoUrl;
            els.modal.classList.add('open');
            els.modal.setAttribute('aria-hidden', 'false');
        }

        function close() {
            var els = getEls();
            if (!els.modal) {
                return;
            }

            els.modal.classList.remove('open');
            els.modal.setAttribute('aria-hidden', 'true');
        }

        function onDocClick(e) {
            var target = e.target;
            if (!target || !target.closest) {
                return;
            }

            if (target.closest('#rosterPhotoClose')) {
                close();
                return;
            }

            if (target.closest('.roster-photo-trigger')) {
                open(e, target.closest('.roster-photo-trigger'));
                return;
            }

            var els = getEls();
            if (els.modal && els.modal.classList.contains('open') && els.modal === target) {
                close();
            }
        }

        function onDocKeydown(e) {
            if (e.key === 'Escape') {
                close();
            }
        }

        if (window.__rosterPhotoHandlers) {
            document.removeEventListener('click', window.__rosterPhotoHandlers.click);
            document.removeEventListener('keydown', window.__rosterPhotoHandlers.keydown);
        }

        document.addEventListener('click', onDocClick);
        document.addEventListener('keydown', onDocKeydown);

        window.__rosterPhotoHandlers = {
            click: onDocClick,
            keydown: onDocKeydown,
        };
    })();
</script>