<?php

namespace App\Controllers;

use App\Config;
use App\Database\Database;
use App\Middleware\AuthMiddleware;

class ActivityLogController
{
    public static function log(string $userType, int $userId, string $action, ?string $details = null): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO activity_logs (user_type, user_id, action, details, created_at)
             VALUES (:user_type, :user_id, :action, :details, NOW())'
        );
        $stmt->execute([
            ':user_type' => $userType,
            ':user_id' => $userId,
            ':action' => $action,
            ':details' => $details,
        ]);
    }

    public static function adminList(): void
    {
        $db = Database::getConnection();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(100, max(1, (int)($_GET['per_page'] ?? 50)));
        $offset = ($page - 1) * $perPage;
        $userType = $_GET['user_type'] ?? null;
        $action = $_GET['action'] ?? null;

        $where = [];
        $params = [];

        if ($userType) {
            $where[] = 'user_type = :user_type';
            $params[':user_type'] = $userType;
        }
        if ($action) {
            $where[] = 'action LIKE :action';
            $params[':action'] = '%' . $action . '%';
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $db->prepare("SELECT COUNT(*) FROM activity_logs $whereClause");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $db->prepare(
            "SELECT id, user_type, user_id, action, details, created_at
             FROM activity_logs
             $whereClause
             ORDER BY created_at DESC
             LIMIT $perPage OFFSET $offset"
        );
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        echo json_encode([
            'logs' => $logs,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int)ceil($total / $perPage),
        ]);
    }
}