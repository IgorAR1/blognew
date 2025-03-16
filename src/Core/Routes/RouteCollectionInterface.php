<?php

namespace App\Core\Routes;

use App\Core\Http\RequestInterface;

interface RouteCollectionInterface
{
    public function getRoutes(): array;

    public function addRoute(RouteInterface $route): void;

    public function findRoute(RequestInterface $request): RouteInterface;
}