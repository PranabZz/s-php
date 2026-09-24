<?php

use Sphp\Core\App;
use Sphp\Core\Database;
use Sphp\Core\Router;
use Sphp\Core\ErrorHandler;

// 1. Load helper functions, environment, and error handler
require_once __DIR__ . '/../Sphp/function.php';
ErrorHandler::register();

// 2. Create Application Container
$app = new App();

// 3. Register Core Service Singletons
$app->singleton('config', function () {
    return require __DIR__ . '/../app/config/config.php';
});

$app->singleton('db', function ($app) {
    return new Database($app->make('config'));
});

$app->singleton('router', function () {
    return new Router();
});

return $app;
