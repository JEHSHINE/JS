<?php

namespace App\Controllers;

use App\Config;
use App\Database\Database;
use App\Middleware\AuthMiddleware;

class PaymentController
{
    public static function create(): void
    {
        $customerId = AuthMiddleware::requireAuth(Config::$settings['jwt_secret']);

        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['orderId']) || empty($data['paymentMethod'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing payment parameters']);
            return;
        }

        $validMethods = ['credit_card', 'debit_card', 'paypal', 'bank_transfer', 'cod'];
        if (!in_array($data['paymentMethod'], $validMethods)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid payment method']);
            return;
        }

        $db = Database::getConnection();

        // Verify order belongs to customer
        $stmt = $db->prepare('SELECT id, total_amount, status FROM orders WHERE id = :id AND customer_id = :customer_id LIMIT 1');
        $stmt->execute([':id' => $data['orderId'], ':customer_id' => $customerId]);
        $order = $stmt->fetch();

        if (!$order) {
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
            return;
        }

        if ($order['status'] !== 'pending') {
            http_response_code(400);
            echo json_encode(['error' => 'Order is not in pending status']);
            return;
        }

        // Check if payment already exists
        $stmt = $db->prepare('SELECT id FROM payments WHERE order_id = :order_id LIMIT 1');
        $stmt->execute([':order_id' => $data['orderId']]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Payment already exists for this order']);
            return;
        }

        $paymentStatus = $data['paymentMethod'] === 'cod' ? 'pending' : 'completed';
        $transactionRef = $data['paymentMethod'] === 'cod'
            ? null
            : 'TXN' . strtoupper(bin2hex(random_bytes(8)));
        $paidAt = $paymentStatus === 'completed' ? date('Y-m-d H:i:s') : null;

        $stmt = $db->prepare(
            'INSERT INTO payments (order_id, payment_method, payment_status, amount, paid_at, transaction_reference)
             VALUES (:order_id, :payment_method, :payment_status, :amount, :paid_at, :transaction_reference)'
        );
        $stmt->execute([
            ':order_id' => $data['orderId'],
            ':payment_method' => $data['paymentMethod'],
            ':payment_status' => $paymentStatus,
            ':amount' => $order['total_amount'],
            ':paid_at' => $paidAt,
            ':transaction_reference' => $transactionRef,
        ]);

        $paymentId = (int)$db->lastInsertId();

        // If payment is completed, update order status to approved
        if ($paymentStatus === 'completed') {
            $stmt = $db->prepare('UPDATE orders SET status = :status, approved_at = NOW() WHERE id = :id');
            $stmt->execute([':status' => 'approved', ':id' => $data['orderId']]);
        }

        echo json_encode([
            'message' => 'Payment processed successfully',
            'paymentId' => $paymentId,
            'paymentStatus' => $paymentStatus,
            'transactionReference' => $transactionRef,
        ]);
    }

    public static function getByOrder(int $orderId): void
    {
        $customerId = AuthMiddleware::requireAuth(Config::$settings['jwt_secret']);

        $db = Database::getConnection();

        // Verify order belongs to customer
        $stmt = $db->prepare('SELECT id FROM orders WHERE id = :id AND customer_id = :customer_id LIMIT 1');
        $stmt->execute([':id' => $orderId, ':customer_id' => $customerId]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
            return;
        }

        $stmt = $db->prepare(
            'SELECT id, order_id, payment_method, payment_status, amount, paid_at, transaction_reference
             FROM payments WHERE order_id = :order_id LIMIT 1'
        );
        $stmt->execute([':order_id' => $orderId]);
        $payment = $stmt->fetch();

        if (!$payment) {
            http_response_code(404);
            echo json_encode(['error' => 'Payment not found for this order']);
            return;
        }

        echo json_encode($payment);
    }

    public static function adminList(): void
    {
        // Admin auth will be checked by middleware
        $db = Database::getConnection();

        $status = $_GET['status'] ?? null;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(50, max(1, (int)($_GET['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;

        $where = '';
        $params = [];
        if ($status) {
            $where = 'WHERE p.payment_status = :status';
            $params[':status'] = $status;
        }

        $countStmt = $db->prepare("SELECT COUNT(*) FROM payments p $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $db->prepare(
            "SELECT p.id, p.order_id, p.payment_method, p.payment_status, p.amount,
                    p.paid_at, p.transaction_reference, o.customer_id, c.name AS customer_name
             FROM payments p
             JOIN orders o ON p.order_id = o.id
             JOIN customers c ON o.customer_id = c.id
             $where
             ORDER BY p.id DESC
             LIMIT $perPage OFFSET $offset"
        );
        $stmt->execute($params);
        $payments = $stmt->fetchAll();

        echo json_encode([
            'payments' => $payments,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int)ceil($total / $perPage),
        ]);
    }
}