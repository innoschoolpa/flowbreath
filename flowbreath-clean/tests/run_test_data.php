<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Tests\MigrationRunner;
use Tests\Seeders\TestDataSeeder;

// Load test database configuration
$config = require __DIR__ . '/config/database.test.php';

try {
    // Create database connection
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // Run migrations
    echo "Running migrations...\n";
    $runner = new MigrationRunner($pdo, __DIR__ . '/migrations');
    $runner->runMigrations();
    echo "Migrations completed.\n";

    // Seed test data
    echo "Seeding test data...\n";
    $seeder = new TestDataSeeder($pdo);
    $seeder->seed();
    echo "Test data seeding completed.\n";

    echo "\nTest data has been successfully created!\n";
    echo "You can now access the website with the following test accounts:\n\n";
    echo "Admin account:\n";
    echo "Email: admin@example.com\n";
    echo "Password: admin123\n\n";
    echo "Regular user accounts:\n";
    echo "Email: user1@example.com\n";
    echo "Password: user123\n\n";
    echo "Email: user2@example.com\n";
    echo "Password: user123\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
} 