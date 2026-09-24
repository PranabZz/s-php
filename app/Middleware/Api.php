<?php

namespace App\Middleware;

use Sphp\Core\ApiController;

class Api extends ApiController
{
    public function handle()
    {
        $this->setCorsHeaders();

        // Auth service removed pending new auth implementation
        return true;
    }
}
