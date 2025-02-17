<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

// Parse request URI
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Route requests
switch ($requestUri) {
    case '/login':
        if ($requestMethod === 'POST') {
            require_once 'login.php';
            exit;
        }
        break;
        
    case '/update-points':
        if ($requestMethod === 'POST') {
            require_once 'update-points.php';
            exit;
        }
        break;
        
    default:
        // Show available endpoints for root URL
        $endpoints = [
            'POST /login' => 'User authentication',
            'POST /update-points' => 'Update user points'
        ];
        
        echo json_encode([
            'status' => 'API is running',
            'endpoints' => $endpoints
        ]);
        break;
}
