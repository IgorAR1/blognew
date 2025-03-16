<?php

namespace App\Core\Middleware;


use App\Core\Http\RequestInterface;
use App\Core\Http\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RequestHandlerInterface
{
    public function handle(RequestInterface $request): ResponseInterface;
}