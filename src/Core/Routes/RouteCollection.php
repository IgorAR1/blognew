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

    public function addRoute(string $method, RouteInterface $route): void
    {
        $this->routes[$method][] = $route;
    }

    public function findRoute(RequestInterface $request): RouteInterface
    {
        $routes = $this->routes[$request->getMethod()] ?? [];
        // TODO: нажно разбивать роуты по методам [GET =>[route],...], ну и с этим учетом оптимизировать поиск
        foreach ($routes as $route) {
            if ($route->matches($request)) {

                return $route;
            }
        }
        throw new NotFoundRouteException("No route matching for method {$request->getMethod()} in {$request->getUri()}");
    }
}