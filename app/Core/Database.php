<?php

namespace Flex\Core;

use PDO;

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = [
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'db' => $_ENV['DB_NAME'] ?? 'flex_cms',
                'user' => $_ENV['DB_USER'] ?? 'root',
                'pass' => $_ENV['DB_PASS'] ?? '',
                'char' => $_ENV['DB_CHAR'] ?? 'utf8mb4'
            ];

            $dsn = "mysql:host={$config['host']};dbname={$config['db']};charset={$config['char']}";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            self::$instance = new PDO($dsn, $config['user'], $config['pass'], $options);
        }

        return self::$instance;
    }

    public static function query(string $sql, array $params = [])
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }

    public static function rollBack(): bool
    {
        return self::getInstance()->rollBack();
    }

    public static function lastInsertId(): string|false
    {
        return self::getInstance()->lastInsertId();
    }
}