<?php

namespace App\Core\Http\Middleware;

use App\Core\Http\RequestInterface;
use App\Core\Http\ResponseInterface;

class Next implements RequestHandlerInterface
{

    public function __construct(private $middleware, private $next)
    {

    }

    public function handle(RequestInterface $request): ResponseInterface
    {
        return $this->middleware->process($request, $this->next);
    }
}