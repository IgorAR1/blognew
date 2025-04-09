<?php

namespace App\Core\Routing;

class RouteFactory implements RouteFactoryInterface
{
    public function create(string $method, string $uri, mixed $controller): RouteInterface
    {
        return new Route($method, $uri, $controller);
    }
}