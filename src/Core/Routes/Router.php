<?php

namespace App\Core\Routes;

use App\Core\Factories\RouteFactoryInterface;
use App\Core\Http\RequestInterface;

class Router
{
    public function __construct(readonly RouteFactoryInterface         $routeFactory,
                                private RouteCollectionInterface       $routeCollection)
    {
    }

    public function getRoutes(): array
    {
        return $this->routeCollection->getRoutes();
    }

//    public function dispatch(RequestInterface $request)//Это хэндлер psr 15!!!!!!!
//    {
//        $route = $this->findRoute($request);
//        $route->bindParameters($request);
//
//
//        $response = $this->controllerDispatcher->dispatch($route->getController(), $route->getParameters());///Это улетить в цепочку мидлваров
//
//
//        return $response;
////        return $route->run();
//    }?????????

    public function findRoute(RequestInterface $request): RouteInterface
    {
        return $this->routeCollection->findRoute($request);
    }
    public function addRoute(RouteInterface $route): void
    {
        $this->routeCollection->addRoute($route);
    }

    public function create(string $method, string $uri, mixed $controller): RouteInterface
    {
        return $this->routeFactory->create($method, $uri, $controller);
    }

    public function get(string $uri, mixed $controller): void
    {
        $this->addRoute($this->create('GET', $uri, $controller));
    }

    public function post(string $uri, mixed $controller): void
    {
        $this->addRoute($this->create('POST', $uri, $controller));
    }

    public function patch(string $uri, mixed $controller): void
    {
        $this->addRoute($this->create('PATCH', $uri, $controller));
    }

    public function delete(string $uri, mixed $controller): void
    {
        $this->addRoute($this->create('DELETE', $uri, $controller));
    }


}