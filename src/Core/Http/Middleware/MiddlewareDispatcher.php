<?php

namespace App\Core\Http\Middleware;

use App\Core\Http\RequestInterface;
use App\Core\Http\Response;
use App\Core\Http\ResponseInterface;
use LogicException;

class MiddlewareDispatcher implements MiddlewareDispatcherInterface
{
    public function __construct(protected array $stack = [])
    {}

    public function process(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->stack[] = function (RequestInterface $request) use ($handler) {
            return $handler->handle($request);
        };

        $response = $this->handle($request);

        array_pop($this->stack);

        return $response;
    }

    public function handle(RequestInterface $request): ResponseInterface
    {
        $resolved = $this->resolve(0);

        return $resolved->handle($request);
    }

    private function resolve(int $index): RequestHandlerInterface
    {
        if (isset($this->stack[$index])) {
            return new class(function (RequestInterface $request) use ($index) {
                $middleware = $this->stack[$index];
                if ($middleware instanceof MiddlewareInterface) {
                    return $middleware->process($request, $this->resolve($index + 1));
                }
                if (is_callable($middleware)) {
                    return $middleware($request, $this->resolve($index + 1));
                }

                throw new LogicException("Unsupported middleware type at index $index");///TODO: гавно пееделать
            }) implements RequestHandlerInterface {
                public function __construct(readonly \Closure $callback) {}
                public function handle(RequestInterface $request): ResponseInterface
                {
                    return ($this->callback)($request);
                }
                public function __invoke(RequestInterface $request)
                {
                    return ($this->callback)($request);
                }
            };
        }

        return new class implements RequestHandlerInterface {///Если последний элемент цепи не дает респонс - исключение потому что блять если выходишь за массив он нулл возвращает
            public function handle(RequestInterface $request): ResponseInterface
            {
                throw new LogicException("unresolved request: middleware stack exhausted with no result");
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