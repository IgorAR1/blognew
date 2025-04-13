<?php

namespace App\Core\Routing;

use Psr\Http\Message\ServerRequestInterface;

class Router
{
    protected array $groupStack = [];

    public function __construct(readonly RouteFactoryInterface    $routeFactory,
                                readonly RouteCollectionInterface $routeCollection)
    {
    }

    public function getRoutes(): array
    {
        return $this->routeCollection->getRoutes();
    }

    public function findRoute(ServerRequestInterface $request): RouteInterface
    {
        return $this->routeCollection->findRoute($request);
    }

    public function addRoute(string $method, string $uri, mixed $controller): RouteInterface
    {
        $lastGroup = [];

        if (!empty($this->groupStack)) {
            $lastGroup = end($this->groupStack);
        }

        if (isset($lastGroup['prefix'])) {
            $prefixes = (array)$lastGroup['prefix'];
            foreach (array_reverse($prefixes) as $prefix) {
                $uri = $this->prefix($uri, $prefix);
            }
        }

        $route = $this->create($method, $uri, $controller);

        if (isset($lastGroup['middleware'])) {
            $middlewares = (array)$lastGroup['middleware'];
            $route->setMiddlewares($middlewares);
        }

        $this->routeCollection->addRoute($method, $route);

        return $route;
    }

    public function group(array $parameters, \Closure $callback): void
    {
        if (!empty($this->groupStack)) {
            $parameters = array_merge_recursive(end($this->groupStack), $parameters);
        }
        $this->groupStack[] = $parameters;

        $callback($this);

        array_pop($this->groupStack);
    }

    public function create(string $method, string $uri, mixed $controller): RouteInterface
    {
        return $this->routeFactory->create($method, $uri, $controller);
    }

    public function get(string $uri, mixed $controller): RouteInterface
    {
        return $this->addRoute('GET', $uri, $controller);
    }

    public function post(string $uri, mixed $controller): RouteInterface
    {
        return $this->addRoute('POST', $uri, $controller);
    }

    public function patch(string $uri, mixed $controller): RouteInterface
    {
        return $this->addRoute('PATCH', $uri, $controller);
    }

    public function put(string $uri, mixed $controller): RouteInterface
    {
        return $this->addRoute('PUT', $uri, $controller);
    }

    public function delete(string $uri, mixed $controller): RouteInterface
    {
        return $this->addRoute('DELETE', $uri, $controller);
    }

    private function prefix(string $uri, mixed $prefix): string
    {
        return trim(trim($prefix, '/') . '/' . trim($uri, '/'), '/') ?: '/';
    }
}