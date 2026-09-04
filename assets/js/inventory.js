document.querySelectorAll('[data-open-update]').forEach((button) => {
    button.addEventListener('click', () => {
        document.getElementById('update-id').value = button.dataset.id;
        document.getElementById('update-price').value = button.dataset.price;
        document.getElementById('update-threshold').value = button.dataset.threshold;
        openModal('update-product-modal');
    });
});

document.querySelectorAll('[data-open-delete]').forEach((button) => {
    button.addEventListener('click', () => {
        document.getElementById('delete-id').value = button.dataset.id;
        document.getElementById('delete-product-name').textContent = button.dataset.name;
        openModal('delete-product-modal');
    });
});
