<?php
require __DIR__ . '/autoload.php';

use App\Database\Connection;

try {
    $pdo = Connection::getInstance();

    // Drop tables outside of transaction
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    $pdo->exec('DROP TABLE IF EXISTS courses');
    $pdo->exec('DROP TABLE IF EXISTS categories');
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

    // Run migrations directly without transaction
    $migrations = [
        '001create_categories.sql',
        '002create_courses.sql',
    ];

    foreach ($migrations as $file) {
        $path = __DIR__ . '/../database/migrations/' . $file;
        if (!file_exists($path)) {
            throw new Exception("Migration file not found: $path");
        }
        $sql = file_get_contents($path);
        $pdo->exec($sql);
        echo "Applied $file\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
