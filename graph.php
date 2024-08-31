<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: LoginFinal.php");
    exit();
}

include 'Database.php';

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$sql = "SELECT week, product_sale_percentage FROM month_weeklysales WHERE week >= (SELECT MAX(week) - 4 FROM month_weeklysales)";
$result = $con->query($sql);

$labels = array();
$salesData = array();

// Initialize an array to store sales data for each week
$weekSales = array_fill(1, 5, 0);

// Fetch and format data for Chart.js
while ($row = $result->fetch_assoc()) {
    $weekSales[$row['week']] = $row['product_sale_percentage'];
}

for ($i = 1; $i <= 5; $i++) {
    $labels[] = "Week " . $i;
    $salesData[] = $weekSales[$i];
}

$con->close();

$data = array(
    "labels" => $labels,
    "salesData" => $salesData
);

echo json_encode($data);

?>
