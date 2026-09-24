<?php

require_once __DIR__ . '/../../Sphp/function.php';
require_once __DIR__ . '/../../Sphp/Core/Database.php';

use Sphp\Core\Database;

function adaptSqlForDriver(string $sql, string $driver): string
{
    $driver = strtolower($driver);

    if ($driver === 'sqlite') {
        $sql = preg_replace('/\b(?:INT|BIGINT|SMALLINT|TINYINT)\s+AUTO_INCREMENT\s+PRIMARY\s+KEY\b/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sql);
        $sql = preg_replace('/\bAUTO_INCREMENT\b/i', '', $sql);
        $sql = preg_replace('/\bNOW\(\)/i', 'CURRENT_TIMESTAMP', $sql);
        $sql = preg_replace('/\bTINYINT\(\d+\)/i', 'INTEGER', $sql);
        $sql = str_replace('`', '', $sql);

        // Extract inline INDEX definitions to separate CREATE INDEX statements
        if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?(\w+)\s*\((.*?)\);/is', $sql, $matches)) {
            $tableName = $matches[1];
            $tableBody = $matches[2];
            $lines = explode("\n", $tableBody);
            $cleanLines = [];
            $extraIndexes = [];
            foreach ($lines as $line) {
                if (preg_match('/^\s*INDEX\s+(\w+)\s*\((.*?)\)\s*,?\s*$/i', trim($line), $idxMatches)) {
                    $extraIndexes[] = "CREATE INDEX IF NOT EXISTS {$idxMatches[1]} ON {$tableName} ({$idxMatches[2]});";
                } else {
                    $cleanLines[] = $line;
                }
            }
            $cleanBody = rtrim(implode("\n", $cleanLines), ", \r\n\t");
            $newCreateTable = "CREATE TABLE IF NOT EXISTS {$tableName} (\n" . $cleanBody . "\n);";
            if (!empty($extraIndexes)) {
                $newCreateTable .= "\n" . implode("\n", $extraIndexes);
            }
            $sql = preg_replace('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?' . preg_quote($tableName, '/') . '\s*\(.*?\);/is', $newCreateTable, $sql);
        }
    } elseif ($driver === 'pgsql' || $driver === 'postgres' || $driver === 'postgresql') {
        $sql = preg_replace('/\b(?:INT|BIGINT|SMALLINT|TINYINT)\s+AUTO_INCREMENT\s+PRIMARY\s+KEY\b/i', 'SERIAL PRIMARY KEY', $sql);
        $sql = preg_replace('/\bAUTO_INCREMENT\b/i', '', $sql);
        $sql = preg_replace('/\bDATETIME\b/i', 'TIMESTAMP', $sql);
        $sql = preg_replace('/\bTINYINT\(\d+\)/i', 'BOOLEAN', $sql);
        $sql = str_replace('`', '', $sql);

        // Extract inline INDEX definitions
        if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?(\w+)\s*\((.*?)\);/is', $sql, $matches)) {
            $tableName = $matches[1];
            $tableBody = $matches[2];
            $lines = explode("\n", $tableBody);
            $cleanLines = [];
            $extraIndexes = [];
            foreach ($lines as $line) {
                if (preg_match('/^\s*INDEX\s+(\w+)\s*\((.*?)\)\s*,?\s*$/i', trim($line), $idxMatches)) {
                    $extraIndexes[] = "CREATE INDEX IF NOT EXISTS {$idxMatches[1]} ON {$tableName} ({$idxMatches[2]});";
                } else {
                    $cleanLines[] = $line;
                }
            }
            $cleanBody = rtrim(implode("\n", $cleanLines), ", \r\n\t");
            $newCreateTable = "CREATE TABLE IF NOT EXISTS {$tableName} (\n" . $cleanBody . "\n);";
            if (!empty($extraIndexes)) {
                $newCreateTable .= "\n" . implode("\n", $extraIndexes);
            }
            $sql = preg_replace('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?' . preg_quote($tableName, '/') . '\s*\(.*?\);/is', $newCreateTable, $sql);
        }
    }

    return $sql;
}

function setupDatabaseFromSqlFiles($sqlDir) {
    try {
        $config = require __DIR__ . '/../config/config.php';
        $driver = strtolower($config['connection'] ?? env('DB_CONNECTION', 'sqlite'));

        if ($driver === 'mysql') {
            $pdo = new PDO("mysql:host={$config['host']};port={$config['port']}", $config['username'], $config['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbName = $config['database'];
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}`");
            $pdo->exec("USE `{$dbName}`");
        }

        $db = new Database($config);

        if ($driver === 'sqlite') {
            $createMigrationsSql = "
                CREATE TABLE IF NOT EXISTS migrations (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    migration VARCHAR(255) NOT NULL,
                    executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ";
        } elseif ($driver === 'pgsql' || $driver === 'postgres' || $driver === 'postgresql') {
            $createMigrationsSql = "
                CREATE TABLE IF NOT EXISTS migrations (
                    id SERIAL PRIMARY KEY,
                    migration VARCHAR(255) NOT NULL,
                    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ";
        } else {
            $createMigrationsSql = "
                CREATE TABLE IF NOT EXISTS `migrations` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `migration` VARCHAR(255) NOT NULL,
                    `executed_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ";
        }

        $db->connection->exec($createMigrationsSql);

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
            $rawSql = file_get_contents($filePath);

            if ($rawSql === false) {
                echo "Failed to read $sqlFile\n";
                continue;
            }

            if (trim($rawSql) === '') {
                echo "Skipping empty file: $sqlFile\n";
                continue;
            }

            echo "Executing $sqlFile for [{$driver}]...\n";
            
            $adaptedSql = adaptSqlForDriver($rawSql, $driver);
            $db->connection->exec($adaptedSql);

            $stmt = $db->connection->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
            $stmt->execute(['migration' => $sqlFile]);

            echo "Successfully executed and recorded $sqlFile\n";
        }

        echo "Database migration complete for [{$driver}].\n";
    } catch (\PDOException $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

$sqlDir = __DIR__;
setupDatabaseFromSqlFiles($sqlDir);