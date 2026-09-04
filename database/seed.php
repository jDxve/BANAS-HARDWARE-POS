<?php

require_once __DIR__ . '/../config/config.php';

const DEFAULT_ADMIN_USER_ID = '627-999-726';
const DEFAULT_ADMIN_NAME    = 'Administrator';
const DEFAULT_ADMIN_PIN     = '000000';

function fail(string $message): never
{
    fwrite(STDERR, "$message\n");
    exit(1);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

echo "Connecting to {$GLOBALS['argv'][0]} @ " . DB_HOST . " ...\n";

try {
    $con = new mysqli(DB_HOST, DB_USER, DB_PASS, null, DB_PORT);
} catch (mysqli_sql_exception $e) {
    fail('Could not connect to MySQL: ' . $e->getMessage());
}

echo "Applying schema...\n";

$schemaPath = __DIR__ . '/schema.sql';
$schemaSql = file_get_contents($schemaPath);
if ($schemaSql === false) {
    fail("Could not read schema file: $schemaPath");
}

try {
    $con->multi_query($schemaSql);
    do {
        if ($result = $con->store_result()) {
            $result->free();
        }
    } while ($con->more_results() && $con->next_result());
} catch (mysqli_sql_exception $e) {
    fail('Failed to apply schema: ' . $e->getMessage());
}

$con->select_db(DB_NAME);

echo "Seeding default admin account...\n";

$existingAdmin = $con->prepare('SELECT id FROM users WHERE user_id = ?');
$existingAdmin->bind_param('s', $adminUserId);
$adminUserId = DEFAULT_ADMIN_USER_ID;
$existingAdmin->execute();
$existingAdmin->store_result();

if ($existingAdmin->num_rows > 0) {
    echo "  Admin account already exists, skipping.\n";
} else {
    $pinHash = password_hash(DEFAULT_ADMIN_PIN, PASSWORD_DEFAULT);
    $insertAdmin = $con->prepare(
        'INSERT INTO users (user_id, name, pin_hash, role) VALUES (?, ?, ?, ?)'
    );
    $role = 'admin';
    $adminName = DEFAULT_ADMIN_NAME;
    $insertAdmin->bind_param('ssss', $adminUserId, $adminName, $pinHash, $role);
    $insertAdmin->execute();
    echo '  Created admin ' . DEFAULT_ADMIN_USER_ID . ' with PIN ' . DEFAULT_ADMIN_PIN . " (change this after first login).\n";
}

echo "Seeding sample inventory...\n";

$countResult = $con->query('SELECT COUNT(*) AS total FROM inventory');
$inventoryCount = (int) $countResult->fetch_assoc()['total'];

if ($inventoryCount > 0) {
    echo "  Inventory already has data, skipping sample rows.\n";
} else {
    $sampleProducts = [
        ['Common Wire Nail 1"', 'Phoenix', '1kg pack', 'Nails', 55.00, 120],
        ['Common Wire Nail 2"', 'Phoenix', '1kg pack', 'Nails', 58.00, 90],
        ['Portland Cement', 'Eagle', '40kg bag', 'Cements', 265.00, 60],
        ['Corrugated Roofing Sheet (Yero)', 'Union Galvasteel', '8ft, gauge 26', 'Roofing Sheets', 385.00, 40],
        ['Flat Latex Paint - White', 'Boysen', '4L pail', 'Paint', 620.00, 25],
        ['Enamel Paint - Red', 'Davies', '1L can', 'Paint', 210.00, 8],
        ['Marine Plywood', 'Generic', '4x8ft, 12mm', 'Plywood', 980.00, 15],
        ['Ordinary Plywood', 'Generic', '4x8ft, 4.2mm', 'Plywood', 350.00, 20],
        ['Deformed Steel Bar 10mm', 'Pag-asa Steel', '6m length', 'Steel Bars', 210.00, 50],
        ['Deformed Steel Bar 12mm', 'Pag-asa Steel', '6m length', 'Steel Bars', 295.00, 45],
    ];

    $insertProduct = $con->prepare(
        'INSERT INTO inventory (product_name, brand, description, category, product_price, stocks, `date`)
         VALUES (?, ?, ?, ?, ?, ?, CURDATE())'
    );
    $insertSalesRow = $con->prepare(
        'INSERT INTO sales (id, product_name, brand, description, stocks, product_sold, `date`)
         VALUES (?, ?, ?, ?, ?, 0, CURDATE())'
    );

    foreach ($sampleProducts as [$name, $brand, $description, $category, $price, $stocks]) {
        $insertProduct->bind_param('ssssdd', $name, $brand, $description, $category, $price, $stocks);
        $insertProduct->execute();
        $newId = $con->insert_id;

        $insertSalesRow->bind_param('issss', $newId, $name, $brand, $description, $stocks);
        $insertSalesRow->execute();
    }

    echo '  Inserted ' . count($sampleProducts) . " sample products.\n";
}

$con->close();

echo "Done.\n";
