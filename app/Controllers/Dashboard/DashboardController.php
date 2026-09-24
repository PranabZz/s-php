<?php

namespace App\Controllers\Dashboard;

use Sphp\Core\ApiController;
use Sphp\Core\View;
use Sphp\Auth\Auth;

/**
 * DashboardController Controller
 */
class DashboardController extends ApiController
{
    public function index()
    {
        $user = Auth::user();
        View::render('dashboard.php', ['user' => $user]);
    }
}
