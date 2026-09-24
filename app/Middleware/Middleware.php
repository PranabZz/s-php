<?php

namespace App\Middleware;

use Sphp\Auth\Auth;

class Middleware
{
    public function handle()
    {
        if (!Auth::check()) {
            redirect('/login', ['error' => 'Please sign in to access this page']);
            exit;
        }
        return true;
    }
}
