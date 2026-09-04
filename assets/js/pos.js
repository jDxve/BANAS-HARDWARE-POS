document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('#category-tabs .tab');
    const panels = document.querySelectorAll('.category-panel');

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            tabs.forEach((t) => t.classList.toggle('is-active', t === tab));
            panels.forEach((panel) => {
                panel.hidden = panel.id !== tab.dataset.target;
            });
        });
    });

    const cart = new Map();
    const cartLinesEl = document.getElementById('cart-lines');
    const cartEmptyEl = document.getElementById('cart-empty');
    const cartTotalEl = document.getElementById('cart-total');
    const checkoutBtn = document.getElementById('checkout-btn');

    function cartTotal() {
        let total = 0;
        cart.forEach((item) => { total += item.price * item.quantity; });
        return total;
    }

    function renderCart() {
        cartLinesEl.innerHTML = '';

        if (cart.size === 0) {
            cartLinesEl.appendChild(cartEmptyEl);
            checkoutBtn.disabled = true;
        } else {
            cart.forEach((item) => {
                const line = document.createElement('div');
                line.className = 'cart-line';
                line.innerHTML = `
                    <div class="cart-line-head">
                        <strong>${escapeHtml(item.name)}</strong>
                        <button type="button" class="btn btn-icon btn-ghost btn-sm" data-remove-item aria-label="Remove">${ICON_TRASH}</button>
                    </div>
                    <div class="cart-line-row">
                        <div class="qty-stepper">
                            <button type="button" class="btn btn-icon btn-sm btn-outline" data-qty-decrement aria-label="Decrease quantity">${ICON_MINUS}</button>
                            <input type="number" class="qty-input" value="${item.quantity}" min="0" step="any">
                            <button type="button" class="btn btn-icon btn-sm btn-outline" data-qty-increment aria-label="Increase quantity">${ICON_PLUS}</button>
                        </div>
                        <span class="cart-line-total">${money(item.price * item.quantity)}</span>
                    </div>
                `;

                line.querySelector('[data-remove-item]').addEventListener('click', () => {
                    cart.delete(item.id);
                    renderCart();
                });

                line.querySelector('[data-qty-decrement]').addEventListener('click', () => setQuantity(item, item.quantity - 1));
                line.querySelector('[data-qty-increment]').addEventListener('click', () => setQuantity(item, item.quantity + 1));
                line.querySelector('.qty-input').addEventListener('change', (event) => {
                    setQuantity(item, parseFloat(event.target.value));
                });

                cartLinesEl.appendChild(line);
            });
            checkoutBtn.disabled = false;
        }

        cartTotalEl.textContent = money(cartTotal());
    }

    function setQuantity(item, quantity) {
        if (isNaN(quantity) || quantity <= 0) {
            cart.delete(item.id);
            renderCart();
            return;
        }
        if (quantity > item.stocks) {
            toast(`Only ${item.stocks} in stock.`, 'danger');
            quantity = item.stocks;
        }
        item.quantity = quantity;
        renderCart();
    }

    document.querySelectorAll('.product-card').forEach((card) => {
        card.addEventListener('click', () => {
            const { id, name, brand, stocks, price } = card.dataset;
            const stockNum = parseFloat(stocks);

            const existing = cart.get(id);
            if (existing) {
                setQuantity(existing, existing.quantity + 1);
                return;
            }
            if (stockNum <= 0) {
                toast('This product is out of stock.', 'danger');
                return;
            }

            cart.set(id, { id, name, brand, price: parseFloat(price), stocks: stockNum, quantity: 1 });
            renderCart();
        });
    });

    const paymentTotalEl = document.getElementById('payment-total');
    const paymentChangeEl = document.getElementById('payment-change');
    const amountReceivedInput = document.getElementById('amount-received');
    const confirmPaymentBtn = document.getElementById('confirm-payment-btn');

    checkoutBtn.addEventListener('click', () => {
        paymentTotalEl.textContent = money(cartTotal());
        amountReceivedInput.value = '';
        paymentChangeEl.textContent = money(0);
        confirmPaymentBtn.disabled = true;
        openModal('payment-modal');
        amountReceivedInput.focus();
    });

    amountReceivedInput.addEventListener('input', () => {
        const received = parseFloat(amountReceivedInput.value.replace(/,/g, ''));
        const total = cartTotal();
        const valid = !isNaN(received) && received >= total;
        paymentChangeEl.textContent = money(valid ? received - total : 0);
        confirmPaymentBtn.disabled = !valid;
    });

    confirmPaymentBtn.addEventListener('click', () => {
        const received = parseFloat(amountReceivedInput.value.replace(/,/g, ''));
        const items = Array.from(cart.values()).map((item) => ({ id: item.id, quantity: item.quantity }));

        confirmPaymentBtn.disabled = true;
        postForm('api/checkout.php', { items: JSON.stringify(items), amount_received: received })
            .then((data) => {
                if (!data.success) {
                    toast(data.message || 'Checkout failed.', 'danger');
                    confirmPaymentBtn.disabled = false;
                    return;
                }
                closeModal('payment-modal');
                showReceipt(data);
                cart.clear();
                renderCart();
            })
            .catch((error) => {
                console.error('Checkout error:', error);
                toast('Checkout failed. Check your connection and try again.', 'danger');
                confirmPaymentBtn.disabled = false;
            });
    });

    function showReceipt(data) {
        const tbody = document.querySelector('#receipt-table tbody');
        tbody.innerHTML = '';
        data.items.forEach((item) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(item.product_name)}</td>
                <td>${item.quantity}</td>
                <td>${money(item.unit_price)}</td>
                <td>${money(item.line_total)}</td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('receipt-total').textContent = money(data.total_amount);
        document.getElementById('receipt-received').textContent = money(data.amount_received);
        document.getElementById('receipt-change').textContent = money(data.change_amount);
        document.getElementById('receipt-id').textContent = data.transaction_id;

        openModal('receipt-modal');
    }

    document.getElementById('print-receipt-btn').addEventListener('click', () => window.print());
    document.getElementById('new-order-btn').addEventListener('click', () => window.location.reload());
});

const ICON_TRASH = '<svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/><path d="M6 7l1 13a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-13"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>';
const ICON_PLUS = '<svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><line x1="12" y1="4" x2="12" y2="20"/><line x1="4" y1="12" x2="20" y2="12"/></svg>';
const ICON_MINUS = '<svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><line x1="4" y1="12" x2="20" y2="12"/></svg>';
