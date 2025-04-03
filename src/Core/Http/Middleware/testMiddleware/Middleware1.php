<?php

namespace App\Core\Http\Middleware\testMiddleware;

use App\Core\Http\Middleware\MiddlewareInterface;
use App\Core\Http\Middleware\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Middleware1 implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        dump('1');
        return $handler->handle($request);
    }
}