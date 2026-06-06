<?php

namespace App;

use Dotenv\Dotenv;

class Config
{
    public static array $settings = [];

    public static function load(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        self::$settings = [
            'db_host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'db_name' => $_ENV['DB_NAME'] ?? 'jehstore',
            'db_user' => $_ENV['DB_USER'] ?? 'root',
            'db_pass' => $_ENV['DB_PASS'] ?? '',
            'db_charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
            'jwt_secret' => $_ENV['JWT_SECRET'] ?? 'change-me',
        ];
    }
}
