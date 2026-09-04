<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in again.']);
    exit();
}

if (!hash_equals(csrf_token(), $_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Your session expired. Please reload the page.']);
    exit();
}

$items = json_decode($_POST['items'] ?? '[]', true);
$amountReceived = round((float) ($_POST['amount_received'] ?? 0), 2);

if (!is_array($items) || count($items) === 0) {
    echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
    exit();
}

$con->begin_transaction();
try {
    $totalAmount = 0.0;
    $lineItems = [];
    foreach ($items as $entry) {
        $id = (int) ($entry['id'] ?? 0);
        $qty = (float) ($entry['quantity'] ?? 0);
        if ($id <= 0 || $qty <= 0) {
            throw new RuntimeException('Invalid item in cart.');
        }

        $lock = $con->prepare('SELECT product_name, brand, description, product_price, stocks FROM inventory WHERE id = ? FOR UPDATE');
        $lock->bind_param('i', $id);
        $lock->execute();
        $product = $lock->get_result()->fetch_assoc();

        if (!$product) {
            throw new RuntimeException('One of the items in your cart no longer exists.');
        }
        if ((float) $product['stocks'] < $qty) {
            throw new RuntimeException('Insufficient stock for ' . $product['product_name'] . '.');
        }

        $unitPrice = (float) $product['product_price'];
        $lineTotal = round($unitPrice * $qty, 2);
        $totalAmount += $lineTotal;

        $updateInventory = $con->prepare('UPDATE inventory SET stocks = stocks - ? WHERE id = ?');
        $updateInventory->bind_param('di', $qty, $id);
        $updateInventory->execute();

        $updateSales = $con->prepare('UPDATE sales SET stocks = stocks - ?, product_sold = product_sold + ? WHERE id = ?');
        $updateSales->bind_param('ddi', $qty, $qty, $id);
        $updateSales->execute();

        $lineItems[] = [
            'inventory_id' => $id,
            'product_name' => $product['product_name'],
            'brand' => $product['brand'],
            'description' => $product['description'],
            'quantity' => $qty,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
        ];
    }

    $totalAmount = round($totalAmount, 2);
    if ($amountReceived < $totalAmount) {
        throw new RuntimeException('Amount received is insufficient.');
    }

    $changeAmount = round($amountReceived - $totalAmount, 2);
    $transactionId = strtoupper(bin2hex(random_bytes(4)));

    $insertTransaction = $con->prepare(
        'INSERT INTO transactions_history (transaction_id, total_amount, amount_received, change_amount) VALUES (?, ?, ?, ?)'
    );
    $insertTransaction->bind_param('sddd', $transactionId, $totalAmount, $amountReceived, $changeAmount);
    $insertTransaction->execute();
    $transactionRowId = $con->insert_id;

    $insertItem = $con->prepare(
        'INSERT INTO transaction_items (transaction_id, inventory_id, product_name, brand, description, quantity, unit_price, line_total)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($lineItems as $line) {
        $insertItem->bind_param(
            'iisssddd',
            $transactionRowId,
            $line['inventory_id'],
            $line['product_name'],
            $line['brand'],
            $line['description'],
            $line['quantity'],
            $line['unit_price'],
            $line['line_total']
        );
        $insertItem->execute();
    }

    $con->commit();
    echo json_encode([
        'success' => true,
        'transaction_id' => $transactionId,
        'total_amount' => $totalAmount,
        'amount_received' => $amountReceived,
        'change_amount' => $changeAmount,
        'items' => $lineItems,
    ]);
} catch (Throwable $e) {
    $con->rollback();
    error_log('Checkout failed: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage() ?: 'Checkout failed.']);
}
