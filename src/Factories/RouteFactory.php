<?php

namespace App\Factories;

use App\Http\Controllers\ControllerInterface;
use App\Routes\Route;

class RouteFactory implements RouteFactoryInterface
{
    public function create(string $method, string $uri, string $controller, string $action): Route
    {
        return new Route($method, $uri, $controller, $action);
    }
}