<?php

namespace App\Routes;

use App\Middleware\MiddlewareInterface;

class Route
{
    public string $controller;

    public string $action;

    public string $method;

    public string $parameters;

    public string $uri;

    /**
     * @var array<MiddlewareInterface>
     */
    public array $middleware;


    public function run()
    {

    }

}