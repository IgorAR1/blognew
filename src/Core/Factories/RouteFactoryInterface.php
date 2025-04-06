<?php

namespace App\Core\Factories;

use App\Core\Routing\RouteInterface;

interface RouteFactoryInterface
{
    public function create(string $method, string $uri, mixed $controller): RouteInterface;
}