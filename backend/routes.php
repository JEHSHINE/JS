<?php

use App\Controllers\AuthController;
use App\Controllers\ProductController;
use App\Controllers\CategoryController;
use App\Controllers\CartController;
use App\Controllers\OrderController;
use App\Controllers\AdminController;
use App\Controllers\AddressController;
use App\Controllers\ReviewController;
use App\Controllers\NotificationController;
use App\Controllers\SearchController;
use App\Controllers\PaymentController;
use App\Controllers\ActivityLogController;

function route_request(string $method, string $path): void
{
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-TOKEN');

    // Handle preflight
    if ($method === 'OPTIONS') {
        http_response_code(200);
        return;
    }

    $path = rtrim($path, '/');

    switch (true) {
        // ── Auth ────────────────────────────────────────────
        case $path === '/api/auth/register' && $method === 'POST':
            AuthController::register();
            break;
        case $path === '/api/auth/login' && $method === 'POST':
            AuthController::login();
            break;
        case $path === '/api/auth/profile' && $method === 'GET':
            AuthController::profile();
            break;
        case $path === '/api/auth/admin/login' && $method === 'POST':
            AuthController::adminLogin();
            break;

        // ── Products ────────────────────────────────────────
        case $path === '/api/products' && $method === 'GET':
            ProductController::list();
            break;
        case preg_match('#^/api/products/(\d+)$#', $path, $matches) && $method === 'GET':
            ProductController::detail((int) $matches[1]);
            break;

        // ── Categories ──────────────────────────────────────
        case $path === '/api/categories' && $method === 'GET':
            CategoryController::list();
            break;
        case preg_match('#^/api/categories/(\d+)/products$#', $path, $matches) && $method === 'GET':
            CategoryController::products((int) $matches[1]);
            break;

        // ── Cart ────────────────────────────────────────────
        case $path === '/api/cart' && $method === 'GET':
            CartController::view();
            break;
        case $path === '/api/cart' && $method === 'POST':
            CartController::addItem();
            break;
        case $path === '/api/cart/remove' && $method === 'POST':
            CartController::removeItem();
            break;

        // ── Orders ──────────────────────────────────────────
        case $path === '/api/orders' && $method === 'GET':
            OrderController::list();
            break;
        case $path === '/api/orders' && $method === 'POST':
            OrderController::create();
            break;
        case preg_match('#^/api/orders/(\d+)$#', $path, $matches) && $method === 'GET':
            OrderController::detail((int) $matches[1]);
            break;

        // ── Addresses ───────────────────────────────────────
        case $path === '/api/addresses' && $method === 'GET':
            AddressController::list();
            break;
        case $path === '/api/addresses' && $method === 'POST':
            AddressController::create();
            break;
        case preg_match('#^/api/addresses/(\d+)$#', $path, $matches) && $method === 'DELETE':
            AddressController::delete((int) $matches[1]);
            break;

        // ── Reviews ─────────────────────────────────────────
        case $path === '/api/reviews' && $method === 'POST':
            ReviewController::create();
            break;
        case preg_match('#^/api/products/(\d+)/reviews$#', $path, $matches) && $method === 'GET':
            ReviewController::getByProduct((int) $matches[1]);
            break;

        // ── Notifications ───────────────────────────────────
        case $path === '/api/notifications' && $method === 'GET':
            NotificationController::list();
            break;
        case $path === '/api/notifications/read' && $method === 'POST':
            NotificationController::markAsRead();
            break;

        // ── Admin: Products ─────────────────────────────────
        case $path === '/api/admin/products' && $method === 'POST':
            AdminController::createProduct();
            break;
        case preg_match('#^/api/admin/products/(\d+)$#', $path, $matches) && $method === 'PUT':
            AdminController::updateProduct((int) $matches[1]);
            break;
        case preg_match('#^/api/admin/products/(\d+)$#', $path, $matches) && $method === 'DELETE':
            AdminController::deleteProduct((int) $matches[1]);
            break;
        case preg_match('#^/api/admin/products/(\d+)/images$#', $path, $matches) && $method === 'POST':
            AdminController::uploadProductImage((int) $matches[1]);
            break;

        // ── Admin: Categories ───────────────────────────────
        case $path === '/api/admin/categories' && $method === 'POST':
            AdminController::createCategory();
            break;

        // ── Admin: Orders ───────────────────────────────────
        case $path === '/api/admin/orders' && $method === 'GET':
            AdminController::listOrders();
            break;
        case preg_match('#^/api/admin/orders/(\d+)/status$#', $path, $matches) && $method === 'PUT':
            AdminController::updateOrderStatus((int) $matches[1]);
            break;

        // ── Search ────────────────────────────────────────────
        case $path === '/api/search' && $method === 'GET':
            SearchController::search();
            break;
        case $path === '/api/search/suggestions' && $method === 'GET':
            SearchController::suggestions();
            break;

        // ── Payments ──────────────────────────────────────────
        case $path === '/api/payments' && $method === 'POST':
            PaymentController::create();
            break;
        case preg_match('#^/api/orders/(\d+)/payment$#', $path, $matches) && $method === 'GET':
            PaymentController::getByOrder((int) $matches[1]);
            break;

        // ── Admin: Dashboard ────────────────────────────────
        case $path === '/api/admin/dashboard' && $method === 'GET':
            AdminController::dashboard();
            break;
        case $path === '/api/admin/payments' && $method === 'GET':
            AdminController::requireAdminCheck();
            PaymentController::adminList();
            break;
        case $path === '/api/admin/activity-logs' && $method === 'GET':
            AdminController::requireAdminCheck();
            ActivityLogController::adminList();
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            break;
    }
}
