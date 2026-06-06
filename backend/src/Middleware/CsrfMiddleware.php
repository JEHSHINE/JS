<?php

namespace App\Middleware;

class CsrfMiddleware
{
    /**
     * Generate a CSRF token and store it in the session.
     */
    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();

        return $token;
    }

    /**
     * Validate a CSRF token from the request.
     */
    public static function validateToken(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }

        // Token expires after 1 hour
        if (time() - ($_SESSION['csrf_token_time'] ?? 0) > 3600) {
            unset($_SESSION['csrf_token']);
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Require a valid CSRF token for state-changing requests.
     */
    public static function requireToken(): void
    {
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN']
                ?? $_POST['_csrf_token']
                ?? null;

            if (!self::validateToken($token)) {
                http_response_code(419);
                echo json_encode(['error' => 'CSRF token validation failed']);
                exit;
            }
        }
    }
}