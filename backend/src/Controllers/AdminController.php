<?php

namespace App\Controllers;

use App\Config;
use App\Database\Database;
use App\Middleware\AuthMiddleware;

class AdminController
{
    public static function requireAdminCheck(): void
    {
        $userId = AuthMiddleware::getAuthenticatedUserId(Config::$settings['jwt_secret']);
        if ($userId === null) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id FROM admins WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $userId]);
        if (!$stmt->fetch()) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden - admin access required']);
            exit;
        }
    }

    private static function requireAdmin(): array
    {
        // Simple admin check - in production, use a separate admin JWT or role claim
        $userId = AuthMiddleware::getAuthenticatedUserId(Config::$settings['jwt_secret']);
        if ($userId === null) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id, role FROM admins WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $userId]);
        $admin = $stmt->fetch();

        if (!$admin) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden - admin access required']);
            exit;
        }

        return $admin;
    }

    // ── Product CRUD ────────────────────────────────────────────

    public static function createProduct(): void
    {
        self::requireAdmin();

        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['title']) || empty($data['price']) || empty($data['categoryId'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing product fields']);
            return;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO products (title, description, price, stock, category_id, created_at)
             VALUES (:title, :description, :price, :stock, :category_id, NOW())'
        );
        $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'] ?? '',
            ':price' => $data['price'],
            ':stock' => $data['stock'] ?? 0,
            ':category_id' => $data['categoryId'],
        ]);

        echo json_encode([
            'message' => 'Product created successfully',
            'productId' => (int)$db->lastInsertId(),
        ]);
    }

    public static function updateProduct(int $id): void
    {
        self::requireAdmin();

        $data = json_decode(file_get_contents('php://input'), true);

        $db = Database::getConnection();
        $fields = [];
        $params = [':id' => $id];

        foreach (['title', 'description', 'price', 'stock', 'category_id'] as $field) {
            if (isset($data[$field])) {
                $dbField = $field === 'category_id' ? 'category_id' : $field;
                $fields[] = "$dbField = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['error' => 'No fields to update']);
            return;
        }

        $fields[] = 'updated_at = NOW()';
        $stmt = $db->prepare('UPDATE products SET ' . implode(', ', $fields) . ' WHERE id = :id');
        $stmt->execute($params);

        echo json_encode(['message' => 'Product updated']);
    }

    public static function deleteProduct(int $id): void
    {
        self::requireAdmin();

        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM products WHERE id = :id');
        $stmt->execute([':id' => $id]);

        echo json_encode(['message' => 'Product deleted']);
    }

    public static function uploadProductImage(int $productId): void
    {
        self::requireAdmin();

        if (!isset($_FILES['image'])) {
            http_response_code(400);
            echo json_encode(['error' => 'No image uploaded']);
            return;
        }

        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid file type. Allowed: JPEG, PNG, WebP']);
            return;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $productId . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save image']);
            return;
        }

        $imagePath = '/uploads/products/' . $filename;
        $isPrimary = isset($_POST['is_primary']) && $_POST['is_primary'] ? 1 : 0;

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO product_images (product_id, image_path, is_primary, uploaded_at)
             VALUES (:product_id, :image_path, :is_primary, NOW())'
        );
        $stmt->execute([
            ':product_id' => $productId,
            ':image_path' => $imagePath,
            ':is_primary' => $isPrimary,
        ]);

        echo json_encode([
            'message' => 'Image uploaded',
            'imagePath' => $imagePath,
            'imageId' => (int)$db->lastInsertId(),
        ]);
    }

    // ── Category CRUD ───────────────────────────────────────────

    public static function createCategory(): void
    {
        self::requireAdmin();

        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Category name is required']);
            return;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO categories (name, description, created_at) VALUES (:name, :description, NOW())'
        );
        $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'] ?? '',
        ]);

        echo json_encode([
            'message' => 'Category created',
            'categoryId' => (int)$db->lastInsertId(),
        ]);
    }

    // ── Order Management ────────────────────────────────────────

    public static function listOrders(): void
    {
        self::requireAdmin();

        $db = Database::getConnection();
        $stmt = $db->query(
            'SELECT o.id, o.status, o.total_amount, o.placed_at, c.name AS customer_name
             FROM orders o
             JOIN customers c ON o.customer_id = c.id
             ORDER BY o.placed_at DESC'
        );
        echo json_encode($stmt->fetchAll());
    }

    public static function updateOrderStatus(int $id): void
    {
        self::requireAdmin();

        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['status'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Status is required']);
            return;
        }

        $validStatuses = ['pending', 'approved', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($data['status'], $validStatuses)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid status. Valid: ' . implode(', ', $validStatuses)]);
            return;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE orders SET status = :status WHERE id = :id');
        $stmt->execute([':status' => $data['status'], ':id' => $id]);

        echo json_encode(['message' => 'Order status updated']);
    }

    // ── Dashboard Stats ─────────────────────────────────────────

    public static function dashboard(): void
    {
        self::requireAdmin();

        $db = Database::getConnection();

        $totalProducts = $db->query('SELECT COUNT(*) FROM products')->fetchColumn();
        $totalOrders = $db->query('SELECT COUNT(*) FROM orders')->fetchColumn();
        $totalCustomers = $db->query('SELECT COUNT(*) FROM customers')->fetchColumn();
        $pendingOrders = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
        $revenue = $db->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'")->fetchColumn();

        echo json_encode([
            'totalProducts' => (int)$totalProducts,
            'totalOrders' => (int)$totalOrders,
            'totalCustomers' => (int)$totalCustomers,
            'pendingOrders' => (int)$pendingOrders,
            'revenue' => (float)($revenue ?: 0),
        ]);
    }
}
