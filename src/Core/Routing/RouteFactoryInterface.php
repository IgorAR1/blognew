<?php

namespace App\Core\Routing;

interface RouteFactoryInterface
{
    public function create(string $method, string $uri, mixed $controller): RouteInterface;
}