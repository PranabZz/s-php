<?php

require_once __DIR__ . '/../../' . 'sphp/function.php';
require_once  __DIR__ . '/../../' . 'sphp/core/Database.php';
require_once  __DIR__ . '/../../' . 'vendor/autoload.php'; // Required for Dotenv

use Sphp\Core\Database;

// Load environment variables from .env file explicitly for this script
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Ensure DB_CONNECTION is set, default to sqlite if not found
if (!isset($_ENV['DB_CONNECTION']) || empty($_ENV['DB_CONNECTION'])) {
    $_ENV['DB_CONNECTION'] = 'sqlite';
}



function setupDatabaseFromSqlFiles(Database $db) {
    $sqlDir = __DIR__;
    
    $db->query("
        CREATE TABLE IF NOT EXISTS migrations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            migration VARCHAR(255) NOT NULL,
            executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $sqlFiles = array_filter(
        scandir($sqlDir),
        fn($file) => 
            is_file($sqlDir . DIRECTORY_SEPARATOR . $file) && 
            pathinfo($file, PATHINFO_EXTENSION) === 'sql' &&
            preg_match('/^\d{14}_/', $file) // Must start with 14-digit timestamp
    );

    if (empty($sqlFiles)) {
        echo "No valid .sql migration files found in $sqlDir\n";
        return;
    }

    sort($sqlFiles);

    $executed = $db->query("SELECT migration FROM migrations");
    $executedFiles = array_column($executed, 'migration');

    foreach ($sqlFiles as $sqlFile) {
        if (in_array($sqlFile, $executedFiles)) {
            echo "Skipping already executed migration: $sqlFile\n";
            continue;
        }

        $filePath = $sqlDir . DIRECTORY_SEPARATOR . $sqlFile;
        $sql = file_get_contents($filePath);

        if ($sql === false) {
            echo "Failed to read $sqlFile\n";
            continue;
        }

        if (trim($sql) === '') {
            echo "Skipping empty file: $sqlFile\n";
            continue;
        }

        echo "Executing $sqlFile...\n";
        
        $db->query($sql);

        $db->query("INSERT INTO migrations (migration) VALUES (:migration)", [
            'migration' => $sqlFile
        ]);

        echo "Successfully executed and recorded $sqlFile\n";
    }

    echo "Database migration complete.\n";
}

try {
    $config = require __DIR__ . '/../config/config.php';
    $db = new Database($config);
    setupDatabaseFromSqlFiles($db);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}