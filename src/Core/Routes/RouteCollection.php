<?php

namespace App\Core\Routes;

use App\Core\Http\RequestInterface;

class RouteCollection implements RouteCollectionInterface
{
    /**
     * @var array<RouteInterface>
     */
    private array $routes = [];

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function addRoute(RouteInterface $route): void
    {
        $this->routes[] = $route;
    }

    public function findRoute(RequestInterface $request): RouteInterface
    {
        foreach ($this->routes as $route) {
            if ($route->matches($request)) {

                return $route;
            }
        }
        throw new NotFoundRouteException("No route matching for method {$request->getMethod()} in {$request->getUri()}");
    }

}