<?php

namespace App\Controllers;

use App\Config;
use App\Database\Database;
use App\Middleware\AuthMiddleware;

class NotificationController
{
    public static function list(): void
    {
        $customerId = AuthMiddleware::requireAuth(Config::$settings['jwt_secret']);

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT id, title, message, is_read, created_at
             FROM notifications
             WHERE customer_id = :customer_id
             ORDER BY created_at DESC
             LIMIT 50'
        );
        $stmt->execute([':customer_id' => $customerId]);

        $notifications = $stmt->fetchAll();

        // Count unread
        $countStmt = $db->prepare(
            'SELECT COUNT(*) AS unread_count
             FROM notifications
             WHERE customer_id = :customer_id AND is_read = 0'
        );
        $countStmt->execute([':customer_id' => $customerId]);
        $unreadCount = (int)$countStmt->fetch()['unread_count'];

        echo json_encode([
            'unreadCount' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    public static function markAsRead(): void
    {
        $customerId = AuthMiddleware::requireAuth(Config::$settings['jwt_secret']);

        $data = json_decode(file_get_contents('php://input'), true);
        $notificationId = $data['notificationId'] ?? null;

        $db = Database::getConnection();
        if ($notificationId) {
            $stmt = $db->prepare(
                'UPDATE notifications SET is_read = 1 WHERE id = :id AND customer_id = :customer_id'
            );
            $stmt->execute([':id' => $notificationId, ':customer_id' => $customerId]);
        } else {
            // Mark all as read
            $stmt = $db->prepare(
                'UPDATE notifications SET is_read = 1 WHERE customer_id = :customer_id'
            );
            $stmt->execute([':customer_id' => $customerId]);
        }

        echo json_encode(['message' => 'Notifications marked as read']);
    }
}
