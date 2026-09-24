<?php

namespace Sphp\Core;

interface Authenticable
{
    public function getId(): string;
    public function getEmail(): string;
    public function getPassword(): string;

}
