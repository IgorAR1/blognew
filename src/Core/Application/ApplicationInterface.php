<?php

namespace App\Core\Application;

use Psr\Container\ContainerInterface;

interface ApplicationInterface extends ContainerInterface
{
    public function config(string $key = ''): array|string;
}