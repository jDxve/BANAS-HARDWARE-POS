function toast(message, type = 'default', duration = 3500) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    const el = document.createElement('div');
    el.className = `toast${type === 'default' ? '' : ` toast-${type}`}`;
    el.textContent = message;
    container.appendChild(el);

    setTimeout(() => el.remove(), duration);
}
