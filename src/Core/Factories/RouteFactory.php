<?php

namespace App\Core\Factories;

use App\Core\Routing\Route;

class RouteFactory implements RouteFactoryInterface
{
    public function create(string $method, string $uri, mixed $controller): Route
    {
        return new Route($method, $uri, $controller);
    }
}