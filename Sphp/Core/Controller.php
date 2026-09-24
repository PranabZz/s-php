<?php


namespace Sphp\Core;

use Sphp\Core\Database;

class Controller
{
    public $env;
    public $db;

    public function __construct()
    {
        $this->env = function_exists('app') && app()->has('config')
            ? app('config')
            : (file_exists(__DIR__ . '/../../app/config/config.php') ? require __DIR__ . '/../../app/config/config.php' : []);

        $this->db = function_exists('app') && app()->has('db')
            ? app('db')
            : new Database($this->env);
    }
}

