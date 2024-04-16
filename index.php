<?php
include 'config.php';
include 'api.php';

header('Content-Type: application/json');
$request_uri = $_SERVER['REQUEST_URI'];
$uri_parts = explode('/', $request_uri);
$endpoint = implode('/', array_slice($uri_parts, 2));
$endpoint = rtrim($endpoint, '/'); 

if ($endpoint === 'register') {
    register();
}
?>
