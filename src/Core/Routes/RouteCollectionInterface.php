<?php

namespace App\Core\Routes;


use Psr\Http\Message\ServerRequestInterface;

interface RouteCollectionInterface
{
    public function getRoutes(): array;

    public function addRoute(string $method, RouteInterface $route): void;

    public function findRoute(ServerRequestInterface $request): RouteInterface;
}