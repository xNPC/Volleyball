document.addEventListener('DOMContentLoaded', function() {
    // Добавляем скрытые поля перед отправкой формы
    const saveButton = document.querySelector('button[data-update-sort]');

    if (saveButton) {
        saveButton.addEventListener('click', function(e) {
            const sortInputs = document.querySelectorAll('.sort-input');
            const form = document.querySelector('form');

            if (form && sortInputs.length > 0) {
                // Удаляем старые скрытые поля
                document.querySelectorAll('.sort-hidden-field').forEach(el => el.remove());

                // Создаем новые скрытые поля
                sortInputs.forEach(input => {
                    const photoId = input.getAttribute('data-id');
                    const value = input.value;

                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'sort_' + photoId;
                    hiddenInput.value = value;
                    hiddenInput.className = 'sort-hidden-field';

                    form.appendChild(hiddenInput);
                });
            }
        });
    }
});
