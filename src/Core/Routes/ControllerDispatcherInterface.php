<?php

namespace App\Core\Routes;

use App\Core\Http\Middleware\MiddlewareInterface;
use App\Core\Http\Middleware\RequestHandlerInterface;

interface ControllerDispatcherInterface extends MiddlewareInterface, RequestHandlerInterface
{
    public function dispatch(array|callable $controller, array $parameters);//ResponseInterface
}