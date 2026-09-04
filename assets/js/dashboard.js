document.getElementById('current-date').textContent = new Date().toLocaleDateString('en-US', {
    year: 'numeric', month: 'long', day: '2-digit',
});
document.getElementById('register-form')?.addEventListener('submit', (event) => {
    const pin = document.getElementById('reg-pin').value;
    const confirmPin = document.getElementById('reg-confirm-pin').value;
    if (pin !== confirmPin) {
        event.preventDefault();
        toast('PINs do not match.', 'danger');
    }
});
document.querySelectorAll('[data-void-transaction]').forEach((button) => {
    button.addEventListener('click', () => {
        document.getElementById('void-transaction-id').value = button.dataset.voidTransaction;
        openModal('void-modal');
    });
});
fetch(window.SALES_CHART_DATA_URL)
    .then((response) => response.json())
    .then(({ labels, salesData }) => {
        const container = document.getElementById('sales-chart');
        container.innerHTML = '';
        labels.forEach((label, index) => {
            const value = Number(salesData[index]) || 0;
            const wrap = document.createElement('div');
            wrap.className = 'chart-bar-wrap';
            wrap.innerHTML = `
                <span class="chart-bar-value">${value.toFixed(0)}%</span>
                <div class="chart-bar" style="height: ${Math.max(value, 1)}%"></div>
                <span class="chart-bar-label">${escapeHtml(label)}</span>
            `;
            container.appendChild(wrap);
        });
    })
    .catch((error) => console.error('Error fetching sales chart data:', error));
fetch(window.INVENTORY_HISTORY_URL)
    .then((response) => response.json())
    .then((rows) => {
        const tbody = document.querySelector('#history-table tbody');
        if (rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No archived inventory yet.</td></tr>';
            return;
        }

        rows.forEach((row) => {
            const tr = document.createElement('tr');
            const formattedDate = new Date(row.date).toLocaleDateString('en-US', {
                year: 'numeric', month: 'long', day: '2-digit',
            });
            tr.innerHTML = `
                <td>${escapeHtml(row.product_name)}</td>
                <td>${escapeHtml(row.brand)}</td>
                <td>${escapeHtml(row.description)}</td>
                <td>${money(parseFloat(row.product_price))}</td>
                <td>${escapeHtml(row.stocks)}</td>
                <td>${formattedDate}</td>
            `;
            tbody.appendChild(tr);
        });
    })
    .catch((error) => console.error('Error fetching inventory history:', error));
