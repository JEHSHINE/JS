<?php

namespace App\Controllers;

use App\Config;
use App\Database\Database;
use App\Middleware\AuthMiddleware;

class CartController
{
    public static function view(): void
    {
        $customerId = AuthMiddleware::requireAuth(Config::$settings['jwt_secret']);

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT ci.id, ci.product_id, ci.quantity, p.title, p.price, pi.image_path
             FROM cart_items ci
             JOIN carts c ON ci.cart_id = c.id
             JOIN products p ON ci.product_id = p.id
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE c.customer_id = :customer_id'
        );
        $stmt->execute([':customer_id' => $customerId]);
        echo json_encode($stmt->fetchAll());
    }

    public static function addItem(): void
    {
        $customerId = AuthMiddleware::requireAuth(Config::$settings['jwt_secret']);

        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['productId']) || empty($data['quantity'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing cart item parameters']);
            return;
        }

        $db = Database::getConnection();

        // Validate product exists and has stock
        $stmt = $db->prepare('SELECT id, stock FROM products WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $data['productId']]);
        $product = $stmt->fetch();
        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            return;
        }
        if ($product['stock'] < $data['quantity']) {
            http_response_code(400);
            echo json_encode(['error' => 'Insufficient stock']);
            return;
        }

        // Find or create cart
        $stmt = $db->prepare('SELECT id FROM carts WHERE customer_id = :customer_id LIMIT 1');
        $stmt->execute([':customer_id' => $customerId]);
        $cart = $stmt->fetch();

        if (!$cart) {
            $stmt = $db->prepare('INSERT INTO carts (customer_id, created_at) VALUES (:customer_id, NOW())');
            $stmt->execute([':customer_id' => $customerId]);
            $cartId = (int)$db->lastInsertId();
        } else {
            $cartId = (int)$cart['id'];
        }

        // Check if item already in cart — update quantity if so
        $stmt = $db->prepare('SELECT id, quantity FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id LIMIT 1');
        $stmt->execute([':cart_id' => $cartId, ':product_id' => $data['productId']]);
        $existing = $stmt->fetch();

        if ($existing) {
            $newQty = $existing['quantity'] + (int)$data['quantity'];
            $stmt = $db->prepare('UPDATE cart_items SET quantity = :qty WHERE id = :id');
            $stmt->execute([':qty' => $newQty, ':id' => $existing['id']]);
            echo json_encode(['message' => 'Cart item quantity updated']);
        } else {
            $stmt = $db->prepare('INSERT INTO cart_items (cart_id, product_id, quantity, added_at) VALUES (:cart_id, :product_id, :quantity, NOW())');
            $stmt->execute([
                ':cart_id' => $cartId,
                ':product_id' => $data['productId'],
                ':quantity' => $data['quantity'],
            ]);
            echo json_encode(['message' => 'Item added to cart']);
        }
    }

    public static function removeItem(): void
    {
        $customerId = AuthMiddleware::requireAuth(Config::$settings['jwt_secret']);

        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['cartItemId'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing cartItemId']);
            return;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'DELETE ci FROM cart_items ci
             JOIN carts c ON ci.cart_id = c.id
             WHERE ci.id = :id AND c.customer_id = :customer_id'
        );
        $stmt->execute([':id' => $data['cartItemId'], ':customer_id' => $customerId]);

        echo json_encode(['message' => 'Item removed from cart']);
    }
}
