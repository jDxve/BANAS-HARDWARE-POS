<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/helpers.php';

require_login();

const POS_CATEGORIES = ['Nails', 'Cements', 'Roofing Sheets', 'Paint', 'Plywood', 'Steel Bars'];

function category_slug(string $category): string
{
    return strtolower(str_replace(' ', '-', $category));
}
function render_product_cards(mysqli $con, ?string $category, string $search): void
{
    $sql = 'SELECT id, product_name, brand, description, stocks, product_price, low_stock_threshold FROM inventory WHERE 1 = 1';
    $types = '';
    $params = [];

    if ($category !== null) {
        $sql .= ' AND category = ?';
        $types .= 's';
        $params[] = $category;
    }
    if ($search !== '') {
        $sql .= ' AND (product_name LIKE CONCAT(\'%\', ?, \'%\') OR brand LIKE CONCAT(\'%\', ?, \'%\') OR description LIKE CONCAT(\'%\', ?, \'%\'))';
        $types .= 'sss';
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }
    $sql .= ' ORDER BY product_name';

    $stmt = $con->prepare($sql);
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo '<div class="empty-state">No products found.</div>';
        return;
    }

    echo '<div class="product-grid">';
    while ($row = $result->fetch_assoc()) {
        $isLow = (float) $row['stocks'] <= (float) $row['low_stock_threshold'];
        $outOfStock = (float) $row['stocks'] <= 0;
        echo '<button type="button" class="product-card' . ($outOfStock ? ' is-disabled' : '') . '" '
            . ($outOfStock ? 'disabled' : '')
            . ' data-id="' . (int) $row['id'] . '"'
            . ' data-name="' . e($row['product_name']) . '"'
            . ' data-brand="' . e($row['brand']) . '"'
            . ' data-description="' . e($row['description']) . '"'
            . ' data-stocks="' . e((string) $row['stocks']) . '"'
            . ' data-price="' . e((string) $row['product_price']) . '">'
            . ($isLow && !$outOfStock ? '<span class="badge badge-danger product-card-badge">Low stock</span>' : '')
            . ($outOfStock ? '<span class="badge badge-danger product-card-badge">Out of stock</span>' : '')
            . '<strong>' . e($row['product_name']) . '</strong>'
            . '<span class="text-muted">' . e($row['brand']) . '</span>'
            . '<span class="product-card-price">&#8369;' . e(peso((float) $row['product_price'])) . '</span>'
            . '<span class="text-muted">' . e((string) $row['stocks']) . ' in stock</span>'
            . '</button>';
    }
    echo '</div>';
}

$searchTerm = trim($_GET['search'] ?? '');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - Banas Hardware POS</title>
    <link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/pages/pos.css">
</head>
<body>
<div class="app">
    <?php
    $activeHref = 'pos.php';
    $navItems = [
        ['href' => 'pos.php', 'icon' => 'cart', 'label' => 'POS'],
    ];
    include 'includes/sidebar.php';
    ?>

    <div class="main">
        <header class="topbar">
            <div class="row">
                <button type="button" class="sidebar-toggle btn btn-icon btn-outline" id="sidebar-toggle" aria-label="Toggle menu"><?= icon('menu') ?></button>
                <h1>Point of Sale</h1>
            </div>
            <form id="search-form" class="input-with-icon" style="width: 280px;">
                <?= icon('search') ?>
                <input class="input" type="text" id="search-input" name="search" placeholder="Search products" value="<?= e($searchTerm) ?>">
            </form>
        </header>

        <div class="page pos-layout">
            <div class="pos-products">
                <div class="category-tabs" id="category-tabs">
                    <button type="button" class="tab is-active" data-target="cat-all">All</button>
                    <?php foreach (POS_CATEGORIES as $category): ?>
                        <button type="button" class="tab" data-target="cat-<?= e(category_slug($category)) ?>"><?= e($category) ?></button>
                    <?php endforeach; ?>
                </div>

                <div id="cat-all" class="category-panel">
                    <?php render_product_cards($con, null, $searchTerm); ?>
                </div>
                <?php foreach (POS_CATEGORIES as $category): ?>
                    <div id="cat-<?= e(category_slug($category)) ?>" class="category-panel" hidden>
                        <?php render_product_cards($con, $category, $searchTerm); ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <aside class="cart-panel card">
                <h2>Current Order</h2>
                <div class="cart-lines" id="cart-lines">
                    <p class="empty-state" id="cart-empty">Cart is empty. Tap a product to add it.</p>
                </div>
                <div class="cart-summary">
                    <div class="row-between"><span>Total</span><strong id="cart-total">&#8369;0.00</strong></div>
                    <button type="button" class="btn btn-primary btn-block" id="checkout-btn" disabled>Checkout</button>
                </div>
            </aside>
        </div>
    </div>
</div>
<div class="modal-backdrop" id="payment-modal">
    <div class="modal">
        <div class="modal-header">
            <h2>Payment</h2>
            <button type="button" class="btn btn-icon btn-ghost" data-modal-close aria-label="Close"><?= icon('x') ?></button>
        </div>
        <div class="row-between"><span>Total Due</span><strong id="payment-total">&#8369;0.00</strong></div>
        <div class="field" style="margin-top: var(--space-4);">
            <label for="amount-received">Amount Received</label>
            <input class="input" type="text" id="amount-received" inputmode="decimal" placeholder="0.00">
        </div>
        <div class="row-between" style="margin-top: var(--space-2);">
            <span class="text-muted">Change</span>
            <strong id="payment-change">&#8369;0.00</strong>
        </div>
        <button type="button" class="btn btn-primary btn-block" id="confirm-payment-btn" style="margin-top: var(--space-5);" disabled>Confirm &amp; Charge</button>
    </div>
</div>
<div class="modal-backdrop" id="receipt-modal">
    <div class="modal">
        <div class="receipt" id="receipt-content">
            <h2 style="text-align:center;">Receipt</h2>
            <p style="text-align:center;" class="text-muted">Ba&ntilde;as Hardware Store<br>Cabasan, Bacacay, Albay</p>
            <table class="table" id="receipt-table">
                <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
                <tbody></tbody>
            </table>
            <div class="receipt-totals">
                <div class="row-between"><span>Total</span><strong id="receipt-total"></strong></div>
                <div class="row-between"><span>Received</span><strong id="receipt-received"></strong></div>
                <div class="row-between"><span>Change</span><strong id="receipt-change"></strong></div>
                <div class="row-between"><span>Transaction ID</span><strong id="receipt-id"></strong></div>
            </div>
        </div>
        <div class="modal-footer no-print">
            <button type="button" class="btn btn-outline" id="print-receipt-btn"><?= icon('printer') ?> Print</button>
            <button type="button" class="btn btn-primary" id="new-order-btn">New Order</button>
        </div>
    </div>
</div>

<script src="assets/js/lib/modal.js"></script>
<script src="assets/js/lib/toast.js"></script>
<script src="assets/js/lib/api.js"></script>
<script src="assets/js/sidebar.js"></script>
<script>
    window.CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;
</script>
<script src="assets/js/pos.js"></script>
</body>
</html>
