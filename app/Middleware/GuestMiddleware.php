<?php

namespace App\Middleware;

use Sphp\Auth\Auth;

class GuestMiddleware
{
    public function handle()
    {
        // Auth check pending new auth implementation
        if (Auth::check()) {
            return redirect('/dashboard', ["message" => "You are already logged in."]);
        }

        return false;
    }
}
