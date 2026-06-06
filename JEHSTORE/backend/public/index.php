<?php
/**
 * JEH STORE - Backend API Entry Point
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config;

// Load environment configuration
Config::load();

// Parse the request
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base path if behind a subfolder
$basePath = '/api';
if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
} else {
    // Support both /api/... and direct /... routing
}

// Route the request
require_once __DIR__ . '/../routes.php';
route_request($method, $path);