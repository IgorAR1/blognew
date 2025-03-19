<?php

namespace App\Core\Http\Middleware\testMiddleware;

use App\Core\Http\Middleware\MiddlewareInterface;
use App\Core\Http\Middleware\RequestHandlerInterface;
use App\Core\Http\RequestInterface;
use App\Core\Http\ResponseInterface;

class Middleware2 implements MiddlewareInterface
{
    public function process(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        dump('2');
        return $handler->handle($request);
    }
}