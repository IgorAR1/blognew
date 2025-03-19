<?php

namespace App\Core\Http\Middleware\testMiddleware;

use App\Core\Http\Middleware\MiddlewareInterface;
use App\Core\Http\Middleware\RequestHandlerInterface;
use App\Core\Http\RequestInterface;
use App\Core\Http\ResponseInterface;

class Middleware1 implements MiddlewareInterface
{
    public function process(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        dump('1');
        return $handler->handle($request);
    }
}