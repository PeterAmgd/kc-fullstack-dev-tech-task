<?php
namespace App\Database;

class Connection
{
    private static $instance = null;

    public static function getInstance(): \PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=db.cc.localhost;dbname=course_catalog;charset=utf8mb4';
            self::$instance = new \PDO($dsn, 'test_user', 'test_password', [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false, // Prevent SQL injection
            ]);
        }
        return self::$instance;
    }
}