<?php

namespace App\Core\Routing;

use Psr\Http\Message\ServerRequestInterface;

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

    public function addRoute(string $method, RouteInterface $route): void
    {
        $this->routes[$method][] = $route;
    }

    public function findRoute(ServerRequestInterface $request): RouteInterface
    {
        $routes = $this->routes[$request->getMethod()] ?? [];

        /**
         * @var $route RouteInterface
         */
        foreach ($routes as $route) {
            if ($route->matches($request)) {
                return $route;
            }
        }

        throw new NotFoundRouteException("No route matching for method {$request->getMethod()} in {$request->getUri()}");
    }
}