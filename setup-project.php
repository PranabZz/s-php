<?php

require_once __DIR__ . '/vendor/autoload.php';

use Faker\Factory;
use Sphp\Core\Database;

// Helper function to set or update a specific environment variable in the .env file
function set_env_variable($key, $value) {
    $env_file = __DIR__ . '/.env';
    if (!file_exists($env_file)) {
        file_put_contents($env_file, '');
    }

    $contents = file_get_contents($env_file);
    if (strpos($contents, $key . '=') !== false) {
        $contents = preg_replace('/^' . preg_quote($key) . '=.*/m', $key . '=' . $value, $contents);
    } else {
        $contents .= "\n" . $key . '=' . $value;
    }
    file_put_contents($env_file, $contents);
}

// Helper function to get a specific environment variable from the .env file
function get_env_variable($key) {
    $env_file = __DIR__ . '/.env';
    if (!file_exists($env_file)) {
        return null;
    }
    $contents = file_get_contents($env_file);
    preg_match('/^' . preg_quote($key) . '=(.*)$/m', $contents, $matches);
    return $matches[1] ?? null;
}

// Ensure .env file exists
$env_file_path = __DIR__ . '/.env';
$env_example_path = __DIR__ . '/.env.example';
if (!file_exists($env_file_path)) {
    echo "Creating .env file from .env.example...\n";
    copy($env_example_path, $env_file_path);
}

// Load environment variables for the script
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "----------------------------------------\n";
echo " S-PHP Project Setup\n";
echo "----------------------------------------\n";

// --- Database Configuration ---
echo "\n--- Database Setup ---\n";
echo "Choose your database driver:\n";
echo "1) sqlite (default)\n";
echo "2) mysql\n";
echo "3) psql\n";

$selected_driver = '';
while (empty($selected_driver)) {
    $choice = trim(readline("Enter choice (1/2/3): "));
    switch ($choice) {
        case '1':
            $selected_driver = 'sqlite';
            break;
        case '2':
            $selected_driver = 'mysql';
            break;
        case '3':
            $selected_driver = 'pgsql';
            break;
        case '': // Default to sqlite if empty
            $selected_driver = 'sqlite';
            break;
        default:
            echo "Invalid choice. Please enter 1, 2, or 3.\n";
            break;
    }
}

echo "Configuring for $selected_driver...\n";
set_env_variable('DB_CONNECTION', $selected_driver);

$db_host = '';
$db_port = '';
$db_name = '';
$db_user = '';
$db_pass = '';

switch ($selected_driver) {
    case 'sqlite':
        set_env_variable('DB_DATABASE', __DIR__ . '/.data/database.sqlite');
        // Remove other DB variables if they exist
        $contents = file_get_contents($env_file_path);
        $contents = preg_replace('/^DB_HOST=.*$/m', '', $contents);
        $contents = preg_replace('/^DB_PORT=.*$/m', '', $contents);
        $contents = preg_replace('/^DB_USERNAME=.*$/m', '', $contents);
        $contents = preg_replace('/^DB_PASSWORD=.*$/m', '', $contents);
        file_put_contents($env_file_path, $contents);
        break;
    case 'mysql':
        $db_host = 'mysql';
        $db_port = '3306';
        $db_name = trim(readline("Enter MySQL database name (default: sphp): ") ?: 'sphp');
        $db_user = trim(readline("Enter MySQL username (default: sphpuser): ") ?: 'sphpuser');
        $db_pass = trim(readline("Enter MySQL password (default: dbpassword): ") ?: 'dbpassword');
        break;
    case 'pgsql':
        $db_host = 'postgres';
        $db_port = '5432';
        $db_name = trim(readline("Enter PostgreSQL database name (default: sphp): ") ?: 'sphp');
        $db_user = trim(readline("Enter PostgreSQL username (default: sphpuser): ") ?: 'sphpuser');
        $db_pass = trim(readline("Enter PostgreSQL password (default: dbpassword): ") ?: 'dbpassword');
        break;
}

if ($selected_driver !== 'sqlite') {
    set_env_variable('DB_HOST', $db_host);
    set_env_variable('DB_PORT', $db_port);
    set_env_variable('DB_DATABASE', $db_name);
    set_env_variable('DB_USERNAME', $db_user);
    set_env_variable('DB_PASSWORD', $db_pass);
}

echo "Database configuration complete.\n";

// --- Run Migrations ---
echo "\n--- Running Migrations ---\n";
// Temporarily set up PDO for migration if not SQLite, to ensure DB exists
if ($selected_driver !== 'sqlite') {
    try {
        $pdo_dsn = '';
        if ($selected_driver === 'mysql') {
            $pdo_dsn = "mysql:host={$db_host};port={$db_port}";
        } elseif ($selected_driver === 'pgsql') {
            $pdo_dsn = "pgsql:host={$db_host};port={$db_port}";
        }
        $pdo = new PDO($pdo_dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}`");
    } catch (PDOException $e) {
        echo "Error connecting to or creating database: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Now run application migrations using the app's init.php
$config = [
    'driver' => get_env_variable('DB_CONNECTION'),
    'host' => get_env_variable('DB_HOST'),
    'port' => get_env_variable('DB_PORT'),
    'database' => get_env_variable('DB_DATABASE'),
    'username' => get_env_variable('DB_USERNAME'),
    'password' => get_env_variable('DB_PASSWORD')
];

try {
    $db = new Database($config);
    $sqlDir = __DIR__ . '/app/Database';
    
    $db->query("\n        CREATE TABLE IF NOT EXISTS migrations (\n            id INTEGER PRIMARY KEY AUTOINCREMENT,\n            migration VARCHAR(255) NOT NULL,\n            executed_at DATETIME DEFAULT CURRENT_TIMESTAMP\n        )\n    ");

    $sqlFiles = array_filter(
        scandir($sqlDir),
        fn($file) => 
            is_file($sqlDir . DIRECTORY_SEPARATOR . $file) && 
            pathinfo($file, PATHINFO_EXTENSION) === 'sql' &&
            preg_match('/^\d{14}_/', $file) // Must start with 14-digit timestamp
    );

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
        
        // Split SQL by semicolon for multiple statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $db->query($statement);
            }
        }

        $db->query("INSERT INTO migrations (migration) VALUES (:migration)", [
            'migration' => $sqlFile
        ]);

        echo "Successfully executed and recorded $sqlFile\n";
    }

    echo "Database migration complete.\n";
} catch (\Exception $e) {
    echo "Error during migrations: " . $e->getMessage() . "\n";
    exit(1);
}

// --- Test User Creation ---
echo "\n--- Test User Setup ---\n";
$create_test_user = trim(readline("Do you want to create a test user (test@sphp.com, password: password123)? (y/N): ") ?: 'n');

if (strtolower($create_test_user) === 'y') {
    echo "Creating test user...\n";
    $faker = Factory::create();
    $test_email = 'test@sphp.com';
    $test_password_hash = password_hash('password123', PASSWORD_DEFAULT);

    // Check if user already exists
    $existing_user = $db->query("SELECT id FROM users WHERE email = :email", ['email' => $test_email]);
    if (empty($existing_user)) {
        $db->query("INSERT INTO users (name, email, password, verified) VALUES (:name, :email, :password, :verified)", [
            'name' => $faker->name,
            'email' => $test_email,
            'password' => $test_password_hash,
            'verified' => 1
        ]);
        echo "Test user 'test@sphp.com' created successfully.\n";
    } else {
        echo "Test user 'test@sphp.com' already exists.\n";
    }
} else {
    echo "Skipping test user creation.\n";
}

echo "\nSetup complete! You can now run 'docker-compose up' to start your application.\n";

