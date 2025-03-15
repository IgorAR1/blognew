<?php

namespace App\Core\Factories;

use App\Core\Routes\RouteInterface;

interface RouteFactoryInterface
{
    public function create(string $method, string $uri,string $controller, string $action): RouteInterface;
}