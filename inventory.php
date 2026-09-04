<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/helpers.php';

require_admin();

const CATEGORIES = ['Nails', 'Cements', 'Roofing Sheets', 'Paint', 'Plywood', 'Steel Bars', 'Other'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    csrf_verify();

    $productName = trim($_POST['product_name'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = in_array($_POST['category'] ?? '', CATEGORIES, true) ? $_POST['category'] : 'Other';
    $price = (float) ($_POST['product_price'] ?? 0);
    $stocks = (float) ($_POST['stocks'] ?? 0);
    $threshold = (float) ($_POST['low_stock_threshold'] ?? 10);

    $stmt = $con->prepare(
        'INSERT INTO inventory (product_name, brand, description, category, product_price, stocks, low_stock_threshold, `date`)
         VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE())'
    );
    $stmt->bind_param('ssssddd', $productName, $brand, $description, $category, $price, $stocks, $threshold);
    $stmt->execute();

    $newId = $con->insert_id;
    $seedSales = $con->prepare(
        'INSERT INTO sales (id, product_name, brand, description, stocks, product_sold, `date`)
         VALUES (?, ?, ?, ?, ?, 0, CURDATE())'
    );
    $seedSales->bind_param('issss', $newId, $productName, $brand, $description, $stocks);
    $seedSales->execute();

    $_SESSION['flash_success'] = "\"$productName\" added to inventory.";
    header('Location: inventory.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_product') {
    csrf_verify();

    $id = (int) ($_POST['id'] ?? 0);
    $stmt = $con->prepare('DELETE FROM inventory WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $_SESSION['flash_success'] = 'Product deleted.';
    header('Location: inventory.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_product') {
    csrf_verify();

    $id = (int) ($_POST['id'] ?? 0);
    $price = (float) ($_POST['product_price'] ?? 0);
    $stocksToAdd = (float) ($_POST['stocks'] ?? 0);
    $threshold = (float) ($_POST['low_stock_threshold'] ?? 10);

    $stmt = $con->prepare(
        'UPDATE inventory SET product_price = ?, stocks = stocks + ?, low_stock_threshold = ?, `date` = CURDATE() WHERE id = ?'
    );
    $stmt->bind_param('dddi', $price, $stocksToAdd, $threshold, $id);
    $stmt->execute();

    $_SESSION['flash_success'] = 'Product updated.';
    header('Location: inventory.php');
    exit();
}

$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);
$search = trim($_GET['search'] ?? '');
$categoryFilter = in_array($_GET['category'] ?? '', CATEGORIES, true) ? $_GET['category'] : '';

$sql = 'SELECT * FROM inventory WHERE 1 = 1';
$types = '';
$params = [];

if ($search !== '') {
    $sql .= ' AND (product_name LIKE CONCAT(\'%\', ?, \'%\') OR description LIKE CONCAT(\'%\', ?, \'%\') OR brand LIKE CONCAT(\'%\', ?, \'%\'))';
    $types .= 'sss';
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}
if ($categoryFilter !== '') {
    $sql .= ' AND category = ?';
    $types .= 's';
    $params[] = $categoryFilter;
}
$sql .= ' ORDER BY product_name';

$stmt = $con->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - Banas Hardware POS</title>
    <link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/pages/inventory.css">
</head>
<body>
<div class="app">
    <?php
    $activeHref = 'inventory.php';
    $navItems = [
        ['href' => 'dashboard.php', 'icon' => 'home', 'label' => 'Dashboard'],
        ['href' => 'sales.php', 'icon' => 'chart', 'label' => 'Sales Report'],
        ['href' => 'inventory.php', 'icon' => 'archive', 'label' => 'Inventory'],
    ];
    include 'includes/sidebar.php';
    ?>

    <div class="main">
        <header class="topbar">
            <div class="row">
                <button type="button" class="sidebar-toggle btn btn-icon btn-outline" id="sidebar-toggle" aria-label="Toggle menu"><?= icon('menu') ?></button>
                <h1>Inventory</h1>
            </div>
            <button type="button" class="btn btn-primary" data-modal-open="add-product-modal"><?= icon('plus') ?> New Product</button>
        </header>

        <div class="page">
            <div class="card">
                <form method="get" class="row" style="margin-bottom: var(--space-4); flex-wrap: wrap;">
                    <div class="input-with-icon" style="flex: 1; min-width: 220px;">
                        <?= icon('search') ?>
                        <input class="input" type="text" name="search" placeholder="Search products" value="<?= e($search) ?>">
                    </div>
                    <select class="input" name="category" style="max-width: 200px;" onchange="this.form.submit()">
                        <option value="">All categories</option>
                        <?php foreach (CATEGORIES as $category): ?>
                            <option value="<?= e($category) ?>" <?= $categoryFilter === $category ? 'selected' : '' ?>><?= e($category) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-outline">Filter</button>
                    <?php if ($search !== '' || $categoryFilter !== ''): ?>
                        <a href="inventory.php" class="btn btn-ghost">Clear</a>
                    <?php endif; ?>
                </form>

                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Product</th>
                                <th>Brand</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stocks</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php $count = 1; ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php $isLow = (float) $row['stocks'] <= (float) $row['low_stock_threshold']; ?>
                                <tr class="<?= $isLow ? 'is-low-stock' : '' ?>">
                                    <td><?= $count++ ?></td>
                                    <td><?= e($row['product_name']) ?></td>
                                    <td><?= e($row['brand']) ?></td>
                                    <td><?= e($row['description']) ?></td>
                                    <td><span class="badge badge-brand"><?= e($row['category']) ?></span></td>
                                    <td>&#8369;<?= e(peso((float) $row['product_price'])) ?></td>
                                    <td>
                                        <?= e((string) $row['stocks']) ?>
                                        <?php if ($isLow): ?><span class="badge badge-danger">Low</span><?php endif; ?>
                                    </td>
                                    <td class="col-actions">
                                        <button type="button" class="btn btn-icon btn-ghost" title="Restock"
                                            data-open-update
                                            data-id="<?= (int) $row['id'] ?>"
                                            data-price="<?= e((string) $row['product_price']) ?>"
                                            data-threshold="<?= e((string) $row['low_stock_threshold']) ?>"><?= icon('edit') ?></button>
                                        <button type="button" class="btn btn-icon btn-ghost" title="Delete"
                                            data-open-delete
                                            data-id="<?= (int) $row['id'] ?>"
                                            data-name="<?= e($row['product_name']) ?>"><?= icon('trash') ?></button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="empty-state">No products found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-backdrop" id="add-product-modal">
    <div class="modal">
        <div class="modal-header">
            <h2>New Product</h2>
            <button type="button" class="btn btn-icon btn-ghost" data-modal-close aria-label="Close"><?= icon('x') ?></button>
        </div>
        <form method="post" class="stack">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add_product">
            <div class="field"><label>Product Name</label><input class="input" type="text" name="product_name" required></div>
            <div class="field"><label>Brand</label><input class="input" type="text" name="brand" required></div>
            <div class="field"><label>Description</label><input class="input" type="text" name="description" required></div>
            <div class="field">
                <label>Category</label>
                <select class="input" name="category" required>
                    <?php foreach (CATEGORIES as $category): ?>
                        <option value="<?= e($category) ?>"><?= e($category) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="row">
                <div class="field" style="flex: 1;"><label>Price</label><input class="input" type="number" step="0.01" min="0" name="product_price" required></div>
                <div class="field" style="flex: 1;"><label>Stocks</label><input class="input" type="number" step="0.01" min="0" name="stocks" required></div>
            </div>
            <div class="field"><label>Low Stock Threshold</label><input class="input" type="number" step="0.01" min="0" name="low_stock_threshold" value="10"></div>
            <button type="submit" class="btn btn-primary btn-block">Save Product</button>
        </form>
    </div>
</div>
<div class="modal-backdrop" id="update-product-modal">
    <div class="modal">
        <div class="modal-header">
            <h2>Update Product</h2>
            <button type="button" class="btn btn-icon btn-ghost" data-modal-close aria-label="Close"><?= icon('x') ?></button>
        </div>
        <form method="post" class="stack">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_product">
            <input type="hidden" name="id" id="update-id">
            <div class="field"><label>Price</label><input class="input" type="number" step="0.01" min="0" name="product_price" id="update-price" required></div>
            <div class="field"><label>Additional Stocks</label><input class="input" type="number" step="0.01" min="0" name="stocks" value="0" required></div>
            <div class="field"><label>Low Stock Threshold</label><input class="input" type="number" step="0.01" min="0" name="low_stock_threshold" id="update-threshold" required></div>
            <button type="submit" class="btn btn-primary btn-block">Update Product</button>
        </form>
    </div>
</div>
<div class="modal-backdrop" id="delete-product-modal">
    <div class="modal">
        <div class="modal-header"><h2>Delete product?</h2></div>
        <p class="text-muted">Delete <strong id="delete-product-name"></strong>? This cannot be undone.</p>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete_product">
            <input type="hidden" name="id" id="delete-id">
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/lib/modal.js"></script>
<script src="assets/js/lib/toast.js"></script>
<script src="assets/js/sidebar.js"></script>
<script src="assets/js/inventory.js"></script>
<?php if ($flashSuccess): ?>
<script>window.addEventListener('load', () => toast(<?= json_encode($flashSuccess) ?>, 'success'));</script>
<?php endif; ?>
</body>
</html>
