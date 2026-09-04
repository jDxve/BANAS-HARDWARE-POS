const ICON_EYE = '<svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.8-7 10-7 10 7 10 7-3.8 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
const ICON_EYE_OFF = '<svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 5.2A10.6 10.6 0 0 1 12 5c6.2 0 10 7 10 7a17.9 17.9 0 0 1-4 4.6M6.5 6.7C4 8.4 2 12 2 12s3.8 7 10 7c1.5 0 2.8-.3 4-.8"/><path d="M9.5 9.7a3 3 0 0 0 4.2 4.2"/></svg>';

document.querySelectorAll('.password-toggle').forEach((button) => {
    button.addEventListener('click', () => {
        const field = document.getElementById(button.dataset.toggleFor);
        const showing = field.type === 'text';
        field.type = showing ? 'password' : 'text';
        button.innerHTML = showing ? ICON_EYE : ICON_EYE_OFF;
        button.setAttribute('aria-label', showing ? 'Show PIN' : 'Hide PIN');
    });
});

document.getElementById('pin')?.addEventListener('input', (event) => {
    event.target.value = event.target.value.replace(/\D/g, '').slice(0, 6);
});
