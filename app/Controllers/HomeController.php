<?php

namespace App\Controllers;

use Sphp\Core\ApiController;
use Sphp\Core\Controller;
use Sphp\Core\Request;
use Sphp\Core\View;

/**
 * HomeController Controller
 */
class HomeController extends ApiController
{

    public function index()
    {
        View::render('welcome.php');
    }

}
