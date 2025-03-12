<?php

namespace App\Factories;

use App\Http\Controllers\ControllerInterface;
use App\Routes\Route;

interface RouteFactoryInterface
{
    public function create(string $method, string $uri,string $controller, string $action): Route;
}