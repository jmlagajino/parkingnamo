<?php
include 'config.php';
include 'api.php';
include 'api-get.php';

header('Content-Type: application/json');
$request_uri = $_SERVER['REQUEST_URI'];
$uri_parts = explode('/', $request_uri);
$endpoint = implode('/', array_slice($uri_parts, 2));
$endpoint = rtrim($endpoint, '/'); 

if ($endpoint === 'register') {
    register($pdo);
} elseif ($endpoint === 'login') {
    login($pdo);
} elseif ($endpoint === 'api/dashboard') {
    dashboard($pdo);
} elseif ($endpoint === 'api/current_month_sales') {
    current_month_sales($pdo);
} elseif (strpos($endpoint, 'api/get_history/') === 0) {
    $plate = end($uri_parts);
    get_history($pdo, $plate);
} elseif ($endpoint === 'api/get_all_balances') {
    echo get_all_balances($pdo);
} elseif ($endpoint === 'api/transaction_data') { // New endpoint for transaction data
    transaction_data($pdo); // Call the function to fetch and display data
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid endpoint']);
}
?>
