<?php

namespace App\Core\Routes;

use App\Core\Http\RequestInterface;
use App\Core\Middleware\MiddlewareInterface;

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
                                private mixed $controller)
    {
        $this->compileRoute();//Конечно славно делать такие вещи лениво
    }

//    public function run()//ResponseInterface
//    {
//       return $this->controllerDispatcher()->dispatch($this->controller, $this->action, $this->parameters);
//    }

//    private function controllerDispatcher(): ControllerDispatcherInterface
//    {
//        return $this->;
//    }
    public function getController(): mixed
    {
        return $this->controller;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getPath(): string
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

    public function setMiddleware(MiddlewareInterface $middleware): static
    {
        $this->middleware[] = $middleware;

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

    public function matches(RequestInterface $request): bool
    {
        return preg_match('#^' . $this->getCompiled() . '$#', $request->getUri());
    }

    public function bindParameters(RequestInterface $request): void
    {
        preg_match('#^' . $this->getCompiled() . '$#', $request->getUri(), $matches, PREG_UNMATCHED_AS_NULL);

        $this->parameters = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }
}