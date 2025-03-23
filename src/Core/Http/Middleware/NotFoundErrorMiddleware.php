<?php

namespace App\Core\Http\Middleware;

use App\Core\Http\RequestInterface;
use App\Core\Http\ResponseInterface;

class NotFoundErrorMiddleware implements MiddlewareInterface
{

    public function process(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        return $response;
    }
}