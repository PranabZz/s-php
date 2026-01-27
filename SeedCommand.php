<?php

require_once __DIR__ . '/vendor/autoload.php';

use Faker\Factory;
use Sphp\Core\Database;

$config = require __DIR__ . '/app/config/config.php';
// Override database path for local seeding
$config['database'] = __DIR__ . '/.data/database.sqlite';
$db = new Database($config);
$faker = Factory::create();

for ($i = 0; $i < 10; $i++) {
    $db->query("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)", [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => password_hash('password', PASSWORD_DEFAULT)
    ]);

    $userId = $db->lastInsertId(); // Assuming lastInsertId() is available

    // Generate token hash
    $token = bin2hex(random_bytes(32)); // 64 char token
    $tokenHash = hash('sha256', $token);

    // Set scopes
    $scopes = json_encode(['*']);

    // Set expiration date (e.g., 1 hour from now)
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $db->query("INSERT INTO user_tokens (user_id, token_hash, scopes, expires_at) VALUES (:user_id, :token_hash, :scopes, :expires_at)", [
        'user_id' => $userId,
        'token_hash' => $tokenHash,
        'scopes' => $scopes,
        'expires_at' => $expiresAt
    ]);
}

echo "Seeded 10 users and their tokens.\n";

