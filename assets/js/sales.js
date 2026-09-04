document.getElementById('current-date').textContent = new Date().toLocaleDateString('en-US', {
    year: 'numeric', month: 'long', day: '2-digit',
});

document.getElementById('print-btn').addEventListener('click', () => window.print());
