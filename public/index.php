<?php

require_once __DIR__ . '/../Sphp/function.php';
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

spl_autoload_register(function ($class) {
    $class = ltrim($class, '\\');
    $base_dir = __DIR__ . '/../';
    $class_path = str_replace('\\', '/', $class) . '.php';
    $file = $base_dir . $class_path;

    if (file_exists($file)) {
        require_once $file;
    }
});



$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);


if (strpos($requestUri, '/api') === 0) {
    require_once __DIR__ . '/../app/router/api.php';
    
} else {
    require_once __DIR__ . '/../app/router/web.php';
  
}
