function openModal(id) {
    document.getElementById(id)?.classList.add('is-open');
}

function closeModal(id) {
    document.getElementById(id)?.classList.remove('is-open');
}

document.addEventListener('click', (event) => {
    const opener = event.target.closest('[data-modal-open]');
    if (opener) {
        openModal(opener.dataset.modalOpen);
        return;
    }

    const closer = event.target.closest('[data-modal-close]');
    if (closer) {
        closeModal(closer.closest('.modal-backdrop').id);
        return;
    }

    if (event.target.classList.contains('modal-backdrop')) {
        event.target.classList.remove('is-open');
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }
    document.querySelectorAll('.modal-backdrop.is-open').forEach((modal) => modal.classList.remove('is-open'));
});
