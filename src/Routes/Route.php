<?php

namespace App\Routes;

use App\Http\Controllers\ControllerInterface;
use App\Middleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;

class Route
{

    public string $parameters;

    /**
     * @var array<MiddlewareInterface>
     */
    public array $middleware;

    public function __construct(private string $method,
                                private string $uri,
                                private string $controller,
                                private        $action,
                                private string $parameter)
    {
    }

    public function run()
    {
        $controller = new $this->controller;

        return $controller->{$this->action}();
    }

    public function getPath(): string
    {
        return $this->uri;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function match(string $method, string $uri): bool
    {
        if ($method === $this->method && $uri === $this->uri) {
            return true;
        }

        return false;
    }

    public function setController(string $controller): static
    {
        $this->controller = $controller;

        return $this;
    }

    public function setMiddleware(MiddlewareInterface $middleware): static
    {
        $this->middleware[] = $middleware;

        return $this;
    }
}