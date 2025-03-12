<?php

namespace App\Routes;

use App\Factories\RouteFactoryInterface;
use App\Http\Request;

class Router
{
    private array $routes = [];


    public function __construct(readonly RouteFactoryInterface $routeFactory)
    {
    }

    public function getRoutes():array
    {
        return $this->routes;
    }
    //RequestInterface
    public function dispatch(Request $request)
    {
        return $this->match($request->getMethod(), $request->getUri())->run();
    }

    public function match(string $method, string $uri): Route
    {
        foreach ($this->routes as $route) {
            if ($route->match($method, $uri)) {
                return $route;
            }
        }
        throw new RoutingException("No route matching for method {$method} in {$uri}");
    }

    public function addRoute(Route $route): void
    {
        $this->routes[] = $route;
    }

    public function create(string $method, string $uri, string $controller, string $action): Route
    {
        return $this->routeFactory->create($method, $uri, $controller, $action);
    }

    public function get(string $uri, string $controller, string $action):void
    {
        $this->addRoute($this->create('GET', $uri, $controller, $action));
    }

    public function post(string $uri,  string $controller, string $action): void
    {
        $this->addRoute($this->create('POST', $uri, $controller, $action));
    }

    public function patch(string $uri,  string $controller, string $action): void
    {
        $this->addRoute($this->create('PATCH', $uri, $controller, $action));
    }

    public function delete(string $uri, string $controller, string $action): void
    {
        $this->addRoute($this->create('DELETE', $uri, $controller, $action));
    }



}