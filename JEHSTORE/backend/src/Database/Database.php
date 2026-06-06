<?php

namespace App\Database;

use App\Config;
use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $settings = Config::$settings;
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $settings['db_host'],
                $settings['db_name'],
                $settings['db_charset']
            );

            self::$connection = new PDO($dsn, $settings['db_user'], $settings['db_pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }

        return self::$connection;
    }
}
