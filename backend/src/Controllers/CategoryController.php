<?php

namespace App\Controllers;

use App\Database\Database;

class CategoryController
{
    public static function list(): void
    {
        $db = Database::getConnection();
        $stmt = $db->query('SELECT id, name, description FROM categories ORDER BY name');
        echo json_encode($stmt->fetchAll());
    }

    public static function products(int $categoryId): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT p.id, p.title, p.description, p.price, p.stock, c.name AS category,
                    pi.image_path
             FROM products p
             JOIN categories c ON p.category_id = c.id
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE p.category_id = :category_id
             ORDER BY p.id'
        );
        $stmt->execute([':category_id' => $categoryId]);
        echo json_encode($stmt->fetchAll());
    }
}
