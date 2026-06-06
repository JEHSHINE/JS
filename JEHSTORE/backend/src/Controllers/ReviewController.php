<?php

namespace App\Controllers;

use App\Config;
use App\Database\Database;
use App\Middleware\AuthMiddleware;

class ReviewController
{
    public static function create(): void
    {
        $customerId = AuthMiddleware::requireAuth(Config::$settings['jwt_secret']);

        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['productId']) || empty($data['rating'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing review fields']);
            return;
        }

        $rating = (int)$data['rating'];
        if ($rating < 1 || $rating > 5) {
            http_response_code(400);
            echo json_encode(['error' => 'Rating must be between 1 and 5']);
            return;
        }

        $db = Database::getConnection();

        // Check product exists
        $stmt = $db->prepare('SELECT id FROM products WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $data['productId']]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            return;
        }

        $stmt = $db->prepare(
            'INSERT INTO reviews (customer_id, product_id, rating, comment, created_at)
             VALUES (:customer_id, :product_id, :rating, :comment, NOW())'
        );
        $stmt->execute([
            ':customer_id' => $customerId,
            ':product_id' => $data['productId'],
            ':rating' => $rating,
            ':comment' => $data['comment'] ?? null,
        ]);

        echo json_encode(['message' => 'Review submitted successfully']);
    }

    public static function getByProduct(int $productId): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT r.id, r.rating, r.comment, r.created_at, c.name AS customer_name
             FROM reviews r
             JOIN customers c ON r.customer_id = c.id
             WHERE r.product_id = :product_id
             ORDER BY r.created_at DESC'
        );
        $stmt->execute([':product_id' => $productId]);
        echo json_encode($stmt->fetchAll());
    }
}
