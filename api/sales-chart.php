<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$result = $con->query(
    'SELECT week, product_sale_percentage FROM month_weeklysales
     WHERE week >= (SELECT MAX(week) - 4 FROM month_weeklysales)'
);

$weekSales = array_fill(1, 5, 0);
while ($row = $result->fetch_assoc()) {
    $week = (int) $row['week'];
    if ($week >= 1 && $week <= 5) {
        $weekSales[$week] = (float) $row['product_sale_percentage'];
    }
}

$labels = [];
$salesData = [];
for ($i = 1; $i <= 5; $i++) {
    $labels[] = "Week $i";
    $salesData[] = $weekSales[$i];
}

header('Content-Type: application/json');
echo json_encode(['labels' => $labels, 'salesData' => $salesData]);
