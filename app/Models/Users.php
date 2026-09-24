<?php

namespace App\Models;

use Sphp\Core\Models;
use Utopia\Auth\Hashes\Bcrypt;

/* TODO */

class Users extends Models
{
    protected $table = "users";
    protected $fillables = ['email', 'name', 'password', 'verified'];


    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email]);
    }

    public static function save($data)
    {
        $hasher = new Bcrypt();
        $data['password'] = $hasher->hash($data['password']);
        return static::create($data);
    }
}
