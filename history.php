<?php

include 'Database.php';

$data = [];

// Query to fetch data from inventory_history table
$sql = "SELECT product_name, brand, description, product_price, stocks, date FROM inventory_history";
$result = $con->query($sql);

if (!$result) {
    die("Query failed: " . $con->error);
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);

$con->close();
?>
