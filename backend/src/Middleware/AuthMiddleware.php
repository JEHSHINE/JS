<?php

namespace App\Middleware;

class AuthMiddleware
{
    /**
     * Generate a simple JWT-compatible token.
     */
    public static function generateToken(int $userId, string $secret): string
    {
        $header = self::base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = self::base64UrlEncode(json_encode([
            'sub' => $userId,
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24 * 7), // 7 days
        ]));
        $signature = self::base64UrlEncode(
            hash_hmac('sha256', "$header.$payload", $secret, true)
        );

        return "$header.$payload.$signature";
    }

    /**
     * Validate a token and return the payload or null.
     */
    public static function validateToken(string $token, string $secret): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;

        $expectedSignature = self::base64UrlEncode(
            hash_hmac('sha256', "$header.$payload", $secret, true)
        );

        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        $data = json_decode(self::base64UrlDecode($payload), true);
        if (!$data || !isset($data['exp']) || $data['exp'] < time()) {
            return null;
        }

        return $data;
    }

    /**
     * Extract and validate the Bearer token from request headers.
     */
    public static function getAuthenticatedUserId(string $secret): ?int
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/^Bearer\s+(.+)$/', $authHeader, $matches)) {
            return null;
        }

        $payload = self::validateToken($matches[1], $secret);
        return $payload['sub'] ?? null;
    }

    /**
     * Require authentication — send 401 and exit if not authenticated.
     */
    public static function requireAuth(string $secret): int
    {
        $userId = self::getAuthenticatedUserId($secret);
        if ($userId === null) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        return $userId;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
