<?php

namespace App\Controllers\Authentication;

use App\Models\Users;
use Sphp\Core\ApiController;
use Sphp\Core\Request;
use Sphp\Core\View;
use Sphp\Auth\Auth;

/**
 * RegisterController Controller
 */
class RegisterController extends ApiController
{
    public function index()
    {
        View::render('register.php');
    }

    public function register()
    {
        $request = Request::request();

        $email = $request['email'] ?? null;
        $password = $request['password'] ?? null;

        if (!$email || !$password) {
            return redirect('register', ['error' => 'All fields are required']);
        }

        Users::save($request);
        $user = Users::findByEmail($email);

        if ($user) {
            Auth::login($user);
            return redirect('dashboard', ['success' => 'User registered successfully']);
        } else {
            return redirect('register', ['error' => 'Failed to register user']);
        }

    }
}
