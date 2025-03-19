<?php

namespace App\Core\Http\Middleware;


use App\Core\Http\RequestInterface;
use App\Core\Http\ResponseInterface;

interface RequestHandlerInterface
{
    public function handle(RequestInterface $request): ResponseInterface;
}