<?php

namespace App\Controllers;

use App\Database\Database;

class ProductController
{
    public static function list(): void
    {
        $db = Database::getConnection();
        $stmt = $db->query(
            'SELECT p.id, p.title, p.description, p.price, p.stock, c.name AS category,
                    pi.image_path
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             ORDER BY p.id'
        );

        echo json_encode($stmt->fetchAll());
    }

    public static function detail(int $id): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id, title, description, price, stock, category_id FROM products WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch();

        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            return;
        }

        echo json_encode($product);
    }
}
