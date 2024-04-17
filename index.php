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
}
else if ($endpoint === 'login') {
    login($pdo);
}
else if ($endpoint === 'api/dashboard') {
    dashboard($pdo);
}
// elseif (strpos($endpoint, 'dashboard') !== false) {
//     $plate = end($uri_parts);
//     get_plate_parking($pdo, $plate);
// }
else{
    echo json_encode(['success' => false, 'message' => 'Invalid endpoint']);
}
?>
