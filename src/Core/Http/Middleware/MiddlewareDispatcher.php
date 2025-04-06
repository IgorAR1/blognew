<?php

namespace App\Core\Http\Middleware;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareDispatcher implements MiddlewareDispatcherInterface
{
    public function __construct(readonly ContainerInterface $container, protected array $stack = [])//Я не знаю насколько хорошей идеей является передача куда либо контейнера, иным вариантом ленивого резолва - передача фабричного замыкания, но читаемость резко ухудшиться
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->stack[] = function (ServerRequestInterface $request) use ($handler) {
            return $handler->handle($request);
        };

        $response = $this->handle($request);

        array_pop($this->stack);

        return $response;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $resolved = $this->resolve(0);

        return $resolved->handle($request);
    }

    private function resolve(int $index): RequestHandlerInterface
    {
        if (isset($this->stack[$index])) {
            return new class(function (ServerRequestInterface $request) use ($index) {
                $middleware = $this->stack[$index];

                if (is_string($middleware)) {
                    $middleware = $this->container->make($middleware);
                }
                if ($middleware instanceof MiddlewareInterface) {
                    return $middleware->process($request, $this->resolve($index + 1));
                }
                if (is_callable($middleware)) {
                    return $middleware($request, $this->resolve($index + 1));
                }

                throw new LogicException("Unsupported middleware type at index $index");///TODO: гавно пееделать
            }) implements RequestHandlerInterface {
                public function __construct(readonly \Closure $callback)
                {
                }

                public function handle(ServerRequestInterface $request): ResponseInterface
                {
                    return ($this->callback)($request);
                }

                public function __invoke(ServerRequestInterface $request)
                {
                    return ($this->callback)($request);
                }
            };
        }

        return new class implements RequestHandlerInterface {///Если последний элемент цепи не дает респонс - исключение потому что блять если выходишь за массив он нулл возвращает
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new LogicException("Unresolved request: middleware stack exhausted with no result");
            }
        };
    }

    public function addMiddleware(callable|MiddlewareInterface|string|array $middleware): void
    {
        if (is_array($middleware)) {
            $this->stack = array_merge($this->stack, $middleware);

            return;
        }
        $this->stack[] = $middleware;
    }

    public function setMiddlewares(callable|MiddlewareInterface|array|string $middlewares): void
    {
        $this->stack = $middlewares;
    }
}