<?php

use App\Controllers\Api\LoginController;
use App\Controllers\HomeController;

use App\Middleware\Api;
use App\Middleware\Pat;
use Sphp\Core\Router;

$api = new Router();

$api->get('/api/health', HomeController::class, 'health');

$api->post('/api/login', LoginController::class, 'login');
$api->get('/api/welcome', HomeController::class, 'welcome', Pat::class);


$api->dispatch();
