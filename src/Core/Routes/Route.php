<?php

namespace App\Core\Routes;

use App\Core\Http\Middleware\MiddlewareInterface;
use App\Core\Http\RequestInterface;

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
                                private mixed $controller,)
//                                private Dispatcher $dispatcher)
    {
        $this->compileRoute();//Конечно славно делать такие вещи лениво
    }

    public function run(RequestInterface $request)//ResponseInterface
    {
//        $this->dispatcher->handle($request);
//       return $this->controllerDispatcher()->dispatch($this->controller, $this->action, $this->parameters);
    }

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
        $uriPath = parse_url($request->getUri(), PHP_URL_PATH);

        return preg_match('#^' . $this->getCompiled() . '$#', $uriPath);
    }

    public function bindParameters(RequestInterface $request): void
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