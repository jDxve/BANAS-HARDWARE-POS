<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/helpers.php';

require_admin();
$registerError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register_clerk') {
    csrf_verify();

    $name = trim($_POST['name'] ?? '');
    $userId = trim($_POST['user_id'] ?? '');
    $pin = trim($_POST['pin'] ?? '');
    $confirmPin = trim($_POST['confirm_pin'] ?? '');

    if ($pin !== $confirmPin) {
        $registerError = 'PINs do not match.';
    } elseif (!preg_match('/^\d{3}-\d{3}-\d{3}$/', $userId)) {
        $registerError = 'Clerk ID must be in the format 000-000-000.';
    } elseif ($name === '' || $pin === '') {
        $registerError = 'All fields are required.';
    } else {
        $check = $con->prepare('SELECT id FROM users WHERE user_id = ?');
        $check->bind_param('s', $userId);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $registerError = 'That Clerk ID is already registered.';
        } else {
            $pinHash = password_hash($pin, PASSWORD_DEFAULT);
            $role = 'clerk';
            $insert = $con->prepare('INSERT INTO users (name, user_id, pin_hash, role) VALUES (?, ?, ?, ?)');
            $insert->bind_param('ssss', $name, $userId, $pinHash, $role);
            $insert->execute();

            $_SESSION['flash_success'] = "Clerk \"$name\" registered successfully.";
            header('Location: dashboard.php');
            exit();
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'void_transaction') {
    csrf_verify();

    $transactionId = trim($_POST['transaction_id'] ?? '');

    $stmt = $con->prepare("SELECT id FROM transactions_history WHERE transaction_id = ? AND status = 'completed'");
    $stmt->bind_param('s', $transactionId);
    $stmt->execute();
    $txn = $stmt->get_result()->fetch_assoc();

    if (!$txn) {
        $_SESSION['flash_error'] = 'Transaction not found or already voided.';
    } else {
        $con->begin_transaction();
        try {
            $items = $con->prepare('SELECT inventory_id, quantity FROM transaction_items WHERE transaction_id = ?');
            $items->bind_param('i', $txn['id']);
            $items->execute();
            $itemRows = $items->get_result();

            while ($item = $itemRows->fetch_assoc()) {
                if ($item['inventory_id'] === null) {
                    continue;
                }
                $restock = $con->prepare('UPDATE inventory SET stocks = stocks + ? WHERE id = ?');
                $restock->bind_param('di', $item['quantity'], $item['inventory_id']);
                $restock->execute();

                $adjustSales = $con->prepare('UPDATE sales SET stocks = stocks + ?, product_sold = GREATEST(product_sold - ?, 0) WHERE id = ?');
                $adjustSales->bind_param('ddi', $item['quantity'], $item['quantity'], $item['inventory_id']);
                $adjustSales->execute();
            }

            $voidedBy = current_user_id();
            $markVoided = $con->prepare(
                "UPDATE transactions_history SET status = 'voided', voided_by = ?, voided_at = NOW() WHERE id = ?"
            );
            $markVoided->bind_param('si', $voidedBy, $txn['id']);
            $markVoided->execute();

            $con->commit();
            $_SESSION['flash_success'] = "Transaction $transactionId voided and stock restored.";
        } catch (Throwable $e) {
            $con->rollback();
            error_log('Void transaction failed: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Could not void that transaction.';
        }
    }

    header('Location: dashboard.php');
    exit();
}

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
$search = trim($_GET['search'] ?? '');
$stmt = $con->prepare(
    "SELECT transaction_id, total_amount, amount_received, change_amount, status, transaction_date
     FROM transactions_history
     WHERE transaction_id LIKE CONCAT('%', ?, '%')
     ORDER BY transaction_date DESC
     LIMIT 200"
);
$stmt->bind_param('s', $search);
$stmt->execute();
$transactions = $stmt->get_result();

$totalRevenue = (float) ($con->query(
    "SELECT COALESCE(SUM(total_amount), 0) AS total FROM transactions_history WHERE status = 'completed' AND WEEK(transaction_date) = WEEK(CURRENT_DATE())"
)->fetch_assoc()['total']);

$totalTransactions = (int) ($con->query(
    "SELECT COUNT(*) AS total FROM transactions_history WHERE status = 'completed' AND WEEK(transaction_date) = WEEK(CURRENT_DATE())"
)->fetch_assoc()['total']);

$lowStock = $con->query(
    'SELECT id, product_name, brand, category, stocks, low_stock_threshold FROM inventory
     WHERE stocks <= low_stock_threshold ORDER BY stocks ASC LIMIT 20'
);
$lowStockCount = $lowStock->num_rows;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Banas Hardware POS</title>
    <link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/pages/dashboard.css">
</head>
<body>
<div class="app">
    <?php
    $activeHref = 'dashboard.php';
    $navItems = [
        ['href' => 'dashboard.php', 'icon' => 'home', 'label' => 'Dashboard'],
        ['href' => 'sales.php', 'icon' => 'chart', 'label' => 'Sales Report'],
        ['href' => 'inventory.php', 'icon' => 'archive', 'label' => 'Inventory'],
    ];
    $sidebarExtra = '<button type="button" class="btn btn-block" data-modal-open="register-modal">' . icon('user-plus') . ' Register Clerk</button>';
    include 'includes/sidebar.php';
    ?>

    <div class="main">
        <header class="topbar">
            <div class="row">
                <button type="button" class="sidebar-toggle btn btn-icon btn-outline" id="sidebar-toggle" aria-label="Toggle menu"><?= icon('menu') ?></button>
                <h1>Dashboard</h1>
            </div>
            <div class="topbar-meta"><?= icon('calendar') ?> <span id="current-date"></span></div>
        </header>

        <div class="page">
            <div class="grid-3">
                <div class="card stat-card">
                    <span class="stat-label">Weekly Revenue</span>
                    <span class="stat-value">&#8369;<?= e(peso($totalRevenue)) ?></span>
                </div>
                <div class="card stat-card">
                    <span class="stat-label">Weekly Transactions</span>
                    <span class="stat-value"><?= e((string) $totalTransactions) ?></span>
                </div>
                <div class="card stat-card <?= $lowStockCount > 0 ? 'stat-card-warning' : '' ?>">
                    <span class="stat-label">Low Stock Items</span>
                    <span class="stat-value"><?= e((string) $lowStockCount) ?></span>
                </div>
            </div>

            <div class="grid-2">
                <div class="card">
                    <h2>Sales Analytics</h2>
                    <p class="text-muted" style="margin-bottom: var(--space-4);">% of stock sold, last 5 weeks</p>
                    <div id="sales-chart"></div>
                </div>

                <div class="card">
                    <h2>Low Stock</h2>
                    <p class="text-muted" style="margin-bottom: var(--space-4);">Items at or below their reorder threshold</p>
                    <?php if ($lowStockCount === 0): ?>
                        <div class="empty-state"><?= icon('check-circle') ?><p>Everything is well stocked.</p></div>
                    <?php else: ?>
                        <ul class="low-stock-list">
                            <?php while ($item = $lowStock->fetch_assoc()): ?>
                                <li>
                                    <div>
                                        <strong><?= e($item['product_name']) ?></strong>
                                        <span class="text-muted"><?= e($item['brand']) ?> &middot; <?= e($item['category']) ?></span>
                                    </div>
                                    <span class="badge badge-danger"><?= e((string) $item['stocks']) ?> left</span>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="row-between" style="margin-bottom: var(--space-4);">
                    <h2>Transaction History</h2>
                    <form action="" method="get" class="input-with-icon" style="width: 280px;">
                        <?= icon('search') ?>
                        <input class="input" type="text" name="search" placeholder="Search transaction no." value="<?= e($search) ?>">
                    </form>
                </div>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Total</th>
                                <th>Received</th>
                                <th>Change</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="col-actions">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($transactions->num_rows > 0): ?>
                            <?php while ($row = $transactions->fetch_assoc()): ?>
                                <tr>
                                    <td><?= e($row['transaction_id']) ?></td>
                                    <td><?= e(peso((float) $row['total_amount'])) ?></td>
                                    <td><?= e(peso((float) $row['amount_received'])) ?></td>
                                    <td><?= e(peso((float) $row['change_amount'])) ?></td>
                                    <td><?= e((new DateTime($row['transaction_date']))->format('M j, Y g:ia')) ?></td>
                                    <td>
                                        <?php if ($row['status'] === 'voided'): ?>
                                            <span class="badge badge-danger">Voided</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Completed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="col-actions">
                                        <?php if ($row['status'] === 'completed'): ?>
                                            <button type="button" class="btn btn-sm btn-outline" data-void-transaction="<?= e($row['transaction_id']) ?>"><?= icon('undo') ?> Void</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="empty-state">No transactions found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h2 style="margin-bottom: var(--space-4);">Last Archived Inventory</h2>
                <div class="table-wrap">
                    <table class="table" id="history-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Brand</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Stocks</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-backdrop" id="register-modal">
    <div class="modal">
        <div class="modal-header">
            <h2>Register Clerk</h2>
            <button type="button" class="btn btn-icon btn-ghost" data-modal-close aria-label="Close"><?= icon('x') ?></button>
        </div>
        <?php if ($registerError !== ''): ?>
            <p class="form-error" style="margin-bottom: var(--space-3);"><?= e($registerError) ?></p>
        <?php endif; ?>
        <form method="post" class="stack" id="register-form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="register_clerk">
            <div class="field">
                <label for="reg-name">Name</label>
                <input class="input" type="text" id="reg-name" name="name" required>
            </div>
            <div class="field">
                <label for="reg-user-id">Clerk ID</label>
                <input class="input" type="text" id="reg-user-id" name="user_id" placeholder="000-000-000" pattern="\d{3}-\d{3}-\d{3}" required>
            </div>
            <div class="field">
                <label for="reg-pin">PIN</label>
                <input class="input" type="password" id="reg-pin" name="pin" required>
            </div>
            <div class="field">
                <label for="reg-confirm-pin">Confirm PIN</label>
                <input class="input" type="password" id="reg-confirm-pin" name="confirm_pin" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>
    </div>
</div>
<div class="modal-backdrop" id="void-modal">
    <div class="modal">
        <div class="modal-header"><h2>Void transaction?</h2></div>
        <p class="text-muted">This restores the sold quantities back to inventory and marks the transaction as voided. This cannot be undone.</p>
        <form method="post" id="void-form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="void_transaction">
            <input type="hidden" name="transaction_id" id="void-transaction-id">
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-danger">Void Transaction</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/lib/modal.js"></script>
<script src="assets/js/lib/toast.js"></script>
<script src="assets/js/lib/api.js"></script>
<script src="assets/js/sidebar.js"></script>
<script>
    window.CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;
    window.SALES_CHART_DATA_URL = 'api/sales-chart.php';
    window.INVENTORY_HISTORY_URL = 'api/inventory-history.php';
</script>
<script src="assets/js/dashboard.js"></script>
<?php if ($flashSuccess): ?>
<script>window.addEventListener('load', () => toast(<?= json_encode($flashSuccess) ?>, 'success'));</script>
<?php endif; ?>
<?php if ($flashError): ?>
<script>window.addEventListener('load', () => toast(<?= json_encode($flashError) ?>, 'danger'));</script>
<?php endif; ?>
</body>
</html>
