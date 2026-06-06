<?php

namespace App\Controllers;

use App\Config;
use App\Database\Database;
use App\Middleware\AuthMiddleware;

class AuthController
{
    public static function register(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['email']) || empty($data['password']) || empty($data['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing registration parameters']);
            return;
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email format']);
            return;
        }

        if (strlen($data['password']) < 6) {
            http_response_code(400);
            echo json_encode(['error' => 'Password must be at least 6 characters']);
            return;
        }

        $db = Database::getConnection();

        // Check if email already exists
        $stmt = $db->prepare('SELECT id FROM customers WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $data['email']]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Email already registered']);
            return;
        }

        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt = $db->prepare('INSERT INTO customers (name, email, password_hash, phone, created_at) VALUES (:name, :email, :password_hash, :phone, NOW())');
        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':password_hash' => $passwordHash,
            ':phone' => $data['phone'] ?? null,
        ]);

        $customerId = (int)$db->lastInsertId();
        $token = AuthMiddleware::generateToken($customerId, Config::$settings['jwt_secret']);

        http_response_code(201);
        echo json_encode([
            'message' => 'Customer registered successfully',
            'token' => $token,
            'customerId' => $customerId,
        ]);
    }

    public static function login(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing login credentials']);
            return;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id, email, password_hash, name FROM customers WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $data['email']]);
        $customer = $stmt->fetch();

        if (!$customer || !password_verify($data['password'], $customer['password_hash'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        $token = AuthMiddleware::generateToken((int)$customer['id'], Config::$settings['jwt_secret']);

        echo json_encode([
            'message' => 'Login successful',
            'token' => $token,
            'customerId' => (int)$customer['id'],
            'name' => $customer['name'],
        ]);
    }

    public static function profile(): void
    {
        $userId = AuthMiddleware::requireAuth(Config::$settings['jwt_secret']);

        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id, name, email, phone, created_at FROM customers WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $userId]);
        $customer = $stmt->fetch();

        if (!$customer) {
            http_response_code(404);
            echo json_encode(['error' => 'Customer not found']);
            return;
        }

        echo json_encode($customer);
    }

    public static function adminLogin(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['username']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing login credentials']);
            return;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id, username, password_hash, role FROM admins WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $data['username']]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($data['password'], $admin['password_hash'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        $token = AuthMiddleware::generateToken((int)$admin['id'], Config::$settings['jwt_secret']);

        echo json_encode([
            'message' => 'Admin login successful',
            'token' => $token,
            'adminId' => (int)$admin['id'],
            'role' => $admin['role'],
        ]);
    }
}
