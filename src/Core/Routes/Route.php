<?php

namespace App\Core\Routes;

use App\Core\Http\Middleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

class Route implements RouteInterface
{

    private array $parameters;

    /**
     * @var array<MiddlewareInterface>
     */
    private array $middleware;

    protected string $compiled;

    public function __construct(private string $method,
                                private string $uri,
                                private mixed  $controller)
//                                private Dispatcher $dispatcher)
    {
        $this->compileRoute();//Конечно славно делать такие вещи лениво
    }

    public function getController(): mixed
    {
        return $this->controller;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setController(string $controller): static
    {
        $this->controller = $controller;

        return $this;
    }

    public function setMiddleware(mixed $middleware): static
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    public function setMiddlewares(array $middlewares): static
    {
        $this->middleware = array_merge($this->middleware, $middlewares);

        return $this;
    }

    public function getCompiled(): string
    {
        return $this->compiled;
    }

    private function compileRoute(): void
    {
        $this->compiled = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $this->uri);;
    }

    public function matches(ServerRequestInterface $request): bool
    {
        $uriPath = parse_url($request->getUri(), PHP_URL_PATH);

        return preg_match('#^' . $this->getCompiled() . '$#', $uriPath);
    }

    public function bindParameters(ServerRequestInterface $request): void
    {
        $uriPath = parse_url($request->getUri(), PHP_URL_PATH);

        preg_match('#^' . $this->getCompiled() . '$#', $uriPath, $matches, PREG_UNMATCHED_AS_NULL);

        $this->parameters = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }
}