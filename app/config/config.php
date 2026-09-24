<?php

require_once __DIR__ . '/../../Sphp/function.php';

/* 
    Here we keep our database host and the database we will be using for the project
*/

return [
    'connection' => env('DB_CONNECTION', 'sqlite'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', env('DB_CONNECTION') === 'pgsql' ? '5432' : '3306'),
    'database' => env('DB_DATABASE', __DIR__ . '/../Database/database.sqlite'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'smtpHost' => env('MAIL_HOST', 'smtp.gmail.com'),
    'smtpPort' => env('MAIL_PORT', 587),
    'smtpUsername' => env('MAIL_USERNAME', ''),
    'smtpPassword' => env('MAIL_PASSWORD', ''),
];
 