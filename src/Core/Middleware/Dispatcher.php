<?php

namespace App\Core\Middleware;

use App\Core\Http\RequestInterface;
use App\Core\Http\ResponseInterface;

class Dispatcher implements MiddlewareInterface, RequestHandlerInterface
{
    public function process(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // TODO: Implement process() method.
    }

    public function handle(RequestInterface $request): ResponseInterface
    {
        // TODO: Implement handle() method.
    }

}