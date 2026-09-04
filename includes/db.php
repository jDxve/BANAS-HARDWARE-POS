<?php
require_once __DIR__ . '/../config/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

set_exception_handler(function (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    die('Something went wrong. Please try again, or contact the administrator if the problem continues.');
});

try {
    $con = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    $con->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(503);
    die('Unable to connect to the database. Please try again later.');
}
