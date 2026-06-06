<?php

namespace App\Controllers;

use App\Config;
use App\Database\Database;
use App\Middleware\AuthMiddleware;

class AddressController
{
    public static function list(): void
    {
        $customerId = AuthMiddleware::requireAuth(Config::$settings['jwt_secret']);

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT id, label, street, city, state, postal_code, country, created_at
             FROM addresses
             WHERE customer_id = :customer_id
             ORDER BY created_at DESC'
        );
        $stmt->execute([':customer_id' => $customerId]);
        echo json_encode($stmt->fetchAll());
    }

    public static function create(): void
    {
        $customerId = AuthMiddleware::requireAuth(Config::$settings['jwt_secret']);

        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['street']) || empty($data['city']) || empty($data['postalCode']) || empty($data['country'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing address fields']);
            return;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO addresses (customer_id, label, street, city, state, postal_code, country, created_at)
             VALUES (:customer_id, :label, :street, :city, :state, :postal_code, :country, NOW())'
        );
        $stmt->execute([
            ':customer_id' => $customerId,
            ':label' => $data['label'] ?? null,
            ':street' => $data['street'],
            ':city' => $data['city'],
            ':state' => $data['state'] ?? null,
            ':postal_code' => $data['postalCode'],
            ':country' => $data['country'],
        ]);

        echo json_encode([
            'message' => 'Address created successfully',
            'addressId' => (int)$db->lastInsertId(),
        ]);
    }

    public static function delete(int $id): void
    {
        $customerId = AuthMiddleware::requireAuth(Config::$settings['jwt_secret']);

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'DELETE FROM addresses WHERE id = :id AND customer_id = :customer_id'
        );
        $stmt->execute([':id' => $id, ':customer_id' => $customerId]);

        echo json_encode(['message' => 'Address deleted']);
    }
}
