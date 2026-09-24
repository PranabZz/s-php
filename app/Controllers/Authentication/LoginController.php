<?php

namespace App\Controllers\Authentication;

use Sphp\Core\ApiController;
use Sphp\Core\Request;
use Sphp\Core\View;
use Sphp\Auth\Auth;

/**
 * LoginController Controller
 */
class LoginController extends ApiController
{
    public function index()
    {
        View::render('login.php');
    }

    public function login()
    {
        $request = Request::request();

        $email = $request['email'];
        $password = $request['password'];

        $credentials = [
            'email' => $email,
            'password' => $password,
        ];

        if (Auth::attempt($credentials)) {
            return redirect('dashboard', ['success' => 'Logged in successfully']);
        }else{
            return redirect('login', ['error' => 'Invalid credentials']);
        }

    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login', ['success' => 'Logged out successfully']);
    }
}
