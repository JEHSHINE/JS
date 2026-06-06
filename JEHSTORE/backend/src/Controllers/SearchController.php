<?php

namespace App\Controllers;

use App\Database\Database;

class SearchController
{
    public static function search(): void
    {
        $query = $_GET['q'] ?? '';
        $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
        $minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
        $maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
        $sortBy = $_GET['sort_by'] ?? 'relevance';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(50, max(1, (int)($_GET['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;

        if (empty(trim($query)) && $categoryId === null && $minPrice === null && $maxPrice === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Search query or filter criteria required']);
            return;
        }

        $db = Database::getConnection();

        $where = [];
        $params = [];

        if (!empty(trim($query))) {
            $where[] = '(p.title LIKE :query OR p.description LIKE :query2)';
            $params[':query'] = '%' . $query . '%';
            $params[':query2'] = '%' . $query . '%';
        }

        if ($categoryId !== null) {
            $where[] = 'p.category_id = :category_id';
            $params[':category_id'] = $categoryId;
        }

        if ($minPrice !== null) {
            $where[] = 'p.price >= :min_price';
            $params[':min_price'] = $minPrice;
        }

        if ($maxPrice !== null) {
            $where[] = 'p.price <= :max_price';
            $params[':max_price'] = $maxPrice;
        }

        $whereClause = implode(' AND ', $where);

        // Count total results
        $countStmt = $db->prepare("SELECT COUNT(*) FROM products p WHERE $whereClause");
        $countStmt->execute($params);
        $totalResults = (int)$countStmt->fetchColumn();

        // Determine sort order
        $orderBy = 'p.id DESC';
        switch ($sortBy) {
            case 'price_asc':
                $orderBy = 'p.price ASC';
                break;
            case 'price_desc':
                $orderBy = 'p.price DESC';
                break;
            case 'newest':
                $orderBy = 'p.created_at DESC';
                break;
            case 'name':
                $orderBy = 'p.title ASC';
                break;
        }

        $stmt = $db->prepare(
            "SELECT p.id, p.title, p.description, p.price, p.stock, c.name AS category,
                    pi.image_path
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE $whereClause
             ORDER BY $orderBy
             LIMIT $perPage OFFSET $offset"
        );
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        echo json_encode([
            'products' => $products,
            'total' => $totalResults,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int)ceil($totalResults / $perPage),
        ]);
    }

    public static function suggestions(): void
    {
        $query = $_GET['q'] ?? '';
        if (empty(trim($query))) {
            echo json_encode([]);
            return;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT DISTINCT p.title FROM products p
             WHERE p.title LIKE :query
             LIMIT 10"
        );
        $stmt->execute([':query' => '%' . $query . '%']);
        $titles = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        echo json_encode($titles);
    }
}