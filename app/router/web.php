<?php


use App\Controllers\HomeController;
use App\Controllers\Authentication\LoginController;
use App\Controllers\Authentication\RegisterController;
use App\Controllers\Dashboard\DashboardController;
use App\Middleware\GuestMiddleware;
use App\Middleware\Middleware;
use Sphp\Core\Router;

$router = new Router();


// ======================
// Public Routes
// ======================

$router->get('/', HomeController::class, 'index');
$router->get('/register', RegisterController::class, 'index');
$router->post('/register', RegisterController::class, 'register', GuestMiddleware::class);
$router->get('/login', LoginController::class, 'index', GuestMiddleware::class);
$router->post('/login', LoginController::class, 'login');
$router->post('/logout', LoginController::class, 'logout');

// ======================
// Protected Routes
// ======================

$router->get('/dashboard', DashboardController::class, 'index', Middleware::class);



$router->dispatch();
