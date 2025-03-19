<?php

namespace App\Core\Http\Middleware;

interface MiddlewareDispatcherInterface extends RequestHandlerInterface
{
    public function addMiddleware(MiddlewareInterface|string|callable $middleware): void;

    public function setMiddlewares(MiddlewareInterface|string|callable|array $middlewares): void;

}