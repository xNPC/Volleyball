import './bootstrap';

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-photo]');
    if (!trigger) {
        return;
    }

    event.preventDefault();

    const store = window.Alpine && window.Alpine.store('photo');
    if (store) {
        store.show(trigger.dataset.photo, trigger.dataset.name || '');
    }
});