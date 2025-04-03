<?php

namespace App\Core\Http\Middleware\testMiddleware;

use App\Core\Http\Middleware\MiddlewareInterface;
use App\Core\Http\Middleware\RequestHandlerInterface;
use App\Core\Http\ServerRequestInterface;
use App\Core\Http\ResponseInterface;

class Middleware3 implements MiddlewareInterface
{
    public function process(\Psr\Http\Message\ServerRequestInterface $request, RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface
    {
        dump('1');
        return $handler->handle($request);
    }
}