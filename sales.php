<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/helpers.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'restart') {
    csrf_verify();

    if ((int) date('N') !== 6) {
        $_SESSION['flash_error'] = 'Restart is only allowed on Saturdays.';
        header('Location: sales.php');
        exit();
    }

    $currentMonth = (int) date('m');
    $currentYear = (int) date('Y');
    $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
    $currentWeek = (int) ceil((int) date('d') / 7);
    if ($totalDaysInMonth > 28) {
        $currentWeek = (int) ceil(((int) date('d') + 1) / 7);
    }

    if ((int) date('j') === 1 && $currentWeek === 1) {
        $deleteMonth = $con->prepare('DELETE FROM month_weeklysales WHERE MONTH(date_column) = ? AND YEAR(date_column) = ?');
        $deleteMonth->bind_param('ii', $currentMonth, $currentYear);
        $deleteMonth->execute();
    }

    $checkWeek = $con->prepare('SELECT id FROM month_weeklysales WHERE week = ? AND MONTH(date_column) = ? AND YEAR(date_column) = ?');
    $checkWeek->bind_param('iii', $currentWeek, $currentMonth, $currentYear);
    $checkWeek->execute();
    $checkWeek->store_result();

    if ($checkWeek->num_rows > 0) {
        $_SESSION['flash_error'] = "Week $currentWeek already exists in the database.";
        header('Location: sales.php');
        exit();
    }

    $totalProductSold = (float) ($con->query('SELECT COALESCE(SUM(product_sold), 0) AS total FROM sales')->fetch_assoc()['total']);
    $totalStocks = (float) ($con->query('SELECT COALESCE(SUM(stocks), 0) AS total FROM sales')->fetch_assoc()['total']);
    $productSalePercentage = $totalStocks > 0 ? ($totalProductSold / $totalStocks) * 100 : 0.0;

    $con->begin_transaction();
    try {
        $insertWeek = $con->prepare(
            'INSERT INTO month_weeklysales (week, stocks, product_sold, product_sale_percentage, date_column) VALUES (?, ?, ?, ?, CURDATE())'
        );
        $insertWeek->bind_param('iddd', $currentWeek, $totalStocks, $totalProductSold, $productSalePercentage);
        $insertWeek->execute();

        $con->query('TRUNCATE TABLE sales');
        $con->query('DELETE FROM inventory_history');
        $con->query(
            'INSERT INTO inventory_history (product_name, brand, description, product_price, `date`, stocks)
             SELECT product_name, brand, description, product_price, `date`, stocks FROM inventory'
        );

        $reseedSales = $con->prepare(
            'INSERT INTO sales (id, product_name, brand, description, stocks, product_sold, `date`)
             SELECT id, product_name, brand, description, stocks, 0, CURDATE() FROM inventory'
        );
        $reseedSales->execute();

        $con->commit();
        $_SESSION['flash_success'] = "Week $currentWeek archived and sales counters reset.";
    } catch (Throwable $e) {
        $con->rollback();
        error_log('Sales restart failed: ' . $e->getMessage());
        $_SESSION['flash_error'] = 'Could not restart sales. Please try again.';
    }

    header('Location: sales.php');
    exit();
}

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $stmt = $con->prepare(
        'SELECT * FROM sales
         WHERE product_name LIKE CONCAT(\'%\', ?, \'%\')
            OR description LIKE CONCAT(\'%\', ?, \'%\')
            OR brand LIKE CONCAT(\'%\', ?, \'%\')
         ORDER BY product_name'
    );
    $stmt->bind_param('sss', $search, $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $con->query('SELECT * FROM sales ORDER BY product_name');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report - Banas Hardware POS</title>
    <link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/pages/sales.css">
</head>
<body>
<div class="app">
    <?php
    $activeHref = 'sales.php';
    $navItems = [
        ['href' => 'dashboard.php', 'icon' => 'home', 'label' => 'Dashboard'],
        ['href' => 'sales.php', 'icon' => 'chart', 'label' => 'Sales Report'],
        ['href' => 'inventory.php', 'icon' => 'archive', 'label' => 'Inventory'],
    ];
    include 'includes/sidebar.php';
    ?>

    <div class="main">
        <header class="topbar no-print">
            <div class="row">
                <button type="button" class="sidebar-toggle btn btn-icon btn-outline" id="sidebar-toggle" aria-label="Toggle menu"><?= icon('menu') ?></button>
                <h1>Sales Report</h1>
            </div>
            <div class="row">
                <div class="topbar-meta"><?= icon('calendar') ?> <span id="current-date"></span></div>
                <button type="button" class="btn btn-outline" data-modal-open="restart-modal"><?= icon('undo') ?> Restart</button>
                <button type="button" class="btn btn-outline" id="print-btn"><?= icon('printer') ?> Print</button>
            </div>
        </header>

        <div class="page">
            <div class="card">
                <div class="row-between no-print" style="margin-bottom: var(--space-4);">
                    <h2>This Week's Sales</h2>
                    <form action="" method="get" class="input-with-icon" style="width: 280px;">
                        <?= icon('search') ?>
                        <input class="input" type="text" name="search" placeholder="Search products" value="<?= e($search) ?>">
                    </form>
                </div>
                <div class="table-wrap" id="print-area">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Product</th>
                                <th>Brand</th>
                                <th>Description</th>
                                <th>Stocks</th>
                                <th>Sold</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php $count = 1; ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $count++ ?></td>
                                    <td><?= e($row['product_name']) ?></td>
                                    <td><?= e($row['brand']) ?></td>
                                    <td><?= e($row['description']) ?></td>
                                    <td><?= e((string) $row['stocks']) ?></td>
                                    <td><?php
                                        $sold = (float) $row['product_sold'];
                                        echo $sold === (float) (int) $sold ? (int) $sold : e((string) $sold);
                                    ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="empty-state">No products found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="restart-modal">
    <div class="modal">
        <div class="modal-header"><h2>Restart weekly sales?</h2></div>
        <p class="text-muted">This archives this week's totals and resets the sold counters. Only available on Saturdays.</p>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="restart">
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-danger">Restart Sales</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/lib/modal.js"></script>
<script src="assets/js/lib/toast.js"></script>
<script src="assets/js/sidebar.js"></script>
<script src="assets/js/sales.js"></script>
<?php if ($flashSuccess): ?>
<script>window.addEventListener('load', () => toast(<?= json_encode($flashSuccess) ?>, 'success'));</script>
<?php endif; ?>
<?php if ($flashError): ?>
<script>window.addEventListener('load', () => toast(<?= json_encode($flashError) ?>, 'danger'));</script>
<?php endif; ?>
</body>
</html>
