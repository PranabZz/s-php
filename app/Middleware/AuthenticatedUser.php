<?php

namespace App\Middleware;

use Sphp\Auth\Auth;

class AuthenticatedUser
{
    public function handle(): bool
    {
        if (!Auth::check()) {
            redirect('/login', ['error' => 'Please sign in to access this page']);
            exit;
        }
        return true;
    }
}
