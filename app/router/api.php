<?php

use App\Controllers\HomeController;

use App\Middleware\Api;
use Sphp\Core\Router;

$api = new Router();

$api->get('/api/welcome', HomeController::class, 'welcome', Api::class);
$api->get('/health', HomeController::class, 'health', Api::class);

$api->dispatch();
