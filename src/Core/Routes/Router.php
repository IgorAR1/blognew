<?php

namespace App\Core\Routes;

use App\Core\Factories\RouteFactoryInterface;
use App\Core\Http\RequestInterface;

class Router
{
    public function __construct(readonly RouteFactoryInterface $routeFactory,
                                private RouteCollectionInterface $routeCollection,
                                readonly ControllerDispatcherInterface $controllerDispatcher)
    {
    }

    public function getRoutes(): array
    {
        return $this->routeCollection->getRoutes();
    }

    public function dispatch(RequestInterface $request)
    {
        $route = $this->routeCollection->findRoute($request);
        $route->bindParameters($request);

        $this->controllerDispatcher->dispatch($route->getController(), $route->getAction(), $route->getParameters());
//        return $route->run();
    }

    public function addRoute(RouteInterface $route): void
    {
        $this->routeCollection->addRoute($route);
    }

    public function create(string $method, string $uri, string $controller, string $action): RouteInterface
    {
        return $this->routeFactory->create($method, $uri, $controller, $action);
    }

    public function get(string $uri, string $controller, string $action): void
    {
        $this->addRoute($this->create('GET', $uri, $controller, $action));
    }

    public function post(string $uri, string $controller, string $action): void
    {
        $this->addRoute($this->create('POST', $uri, $controller, $action));
    }

    public function patch(string $uri, string $controller, string $action): void
    {
        $this->addRoute($this->create('PATCH', $uri, $controller, $action));
    }

    public function delete(string $uri, string $controller, string $action): void
    {
        $this->addRoute($this->create('DELETE', $uri, $controller, $action));
    }
}