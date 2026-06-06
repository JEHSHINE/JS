<?php

namespace App\Controllers;

use App\Config;
use App\Database\Database;
use App\Middleware\AuthMiddleware;

class OrderController
{
    public static function create(): void
    {
        $customerId = AuthMiddleware::requireAuth(Config::$settings['jwt_secret']);

        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['shippingAddressId']) || empty($data['items']) || !is_array($data['items'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing order parameters']);
            return;
        }

        $db = Database::getConnection();

        // Calculate total from items
        $totalAmount = 0;
        foreach ($data['items'] as $item) {
            $totalAmount += ($item['unitPrice'] * $item['quantity']);
        }

        $stmt = $db->prepare(
            'INSERT INTO orders (customer_id, shipping_address_id, status, total_amount, placed_at)
             VALUES (:customer_id, :address_id, :status, :total_amount, NOW())'
        );
        $stmt->execute([
            ':customer_id' => $customerId,
            ':address_id' => $data['shippingAddressId'],
            ':status' => 'pending',
            ':total_amount' => $totalAmount,
        ]);

        $orderId = (int)$db->lastInsertId();
        $itemStmt = $db->prepare(
            'INSERT INTO order_items (order_id, product_id, quantity, unit_price)
             VALUES (:order_id, :product_id, :quantity, :unit_price)'
        );

        foreach ($data['items'] as $item) {
            $itemStmt->execute([
                ':order_id' => $orderId,
                ':product_id' => $item['productId'],
                ':quantity' => $item['quantity'],
                ':unit_price' => $item['unitPrice'],
            ]);
        }

        // Clear the customer's cart after order placement
        $stmt = $db->prepare('DELETE ci FROM cart_items ci JOIN carts c ON ci.cart_id = c.id WHERE c.customer_id = :customer_id');
        $stmt->execute([':customer_id' => $customerId]);

        echo json_encode([
            'message' => 'Order created successfully',
            'orderId' => $orderId,
            'totalAmount' => $totalAmount,
        ]);
    }

    public static function list(): void
    {
        $customerId = AuthMiddleware::requireAuth(Config::$settings['jwt_secret']);

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT o.id, o.status, o.total_amount, o.placed_at, o.approved_at, o.delivered_at
             FROM orders o
             WHERE o.customer_id = :customer_id
             ORDER BY o.placed_at DESC'
        );
        $stmt->execute([':customer_id' => $customerId]);
        $orders = $stmt->fetchAll();

        // Fetch items for each order
        foreach ($orders as &$order) {
            $stmt = $db->prepare(
                'SELECT oi.product_id, oi.quantity, oi.unit_price, p.title
                 FROM order_items oi
                 JOIN products p ON oi.product_id = p.id
                 WHERE oi.order_id = :order_id'
            );
            $stmt->execute([':order_id' => $order['id']]);
            $order['items'] = $stmt->fetchAll();
        }

        echo json_encode($orders);
    }

    public static function detail(int $id): void
    {
        $customerId = AuthMiddleware::requireAuth(Config::$settings['jwt_secret']);

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT o.* FROM orders o WHERE o.id = :id AND o.customer_id = :customer_id LIMIT 1'
        );
        $stmt->execute([':id' => $id, ':customer_id' => $customerId]);
        $order = $stmt->fetch();

        if (!$order) {
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
            return;
        }

        $stmt = $db->prepare(
            'SELECT oi.product_id, oi.quantity, oi.unit_price, p.title, pi.image_path
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE oi.order_id = :order_id'
        );
        $stmt->execute([':order_id' => $id]);
        $order['items'] = $stmt->fetchAll();

        echo json_encode($order);
    }
}
