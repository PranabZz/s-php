<?php

namespace App\Controllers;

use Sphp\Auth\Auth;
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
        if(Auth::check()){
          $user = Auth::user();
          View::render('welcome.php', ['user' => $user]);
          return;
        }

        View::render('welcome.php');
    }

}
