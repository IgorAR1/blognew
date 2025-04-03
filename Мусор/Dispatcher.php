<?php

namespace App\Core\Http\Middleware;

use App\Core\Http\ServerRequestInterface;
use App\Core\Http\ResponseInterface;
use LogicException;

class Dispatcher implements MiddlewareInterface, RequestHandlerInterface, MiddlewareDispatcherInterface
{
    protected RequestHandlerInterface $handler;
//    /**
//     * @var array<MiddlewareInterface>
//     */
    public function __construct(protected array $stack = [])
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->stack[] = function (ServerRequestInterface $request) use ($handler) { return $handler->handle($request); };;

        return $this->handle($request);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $resolved = $this->resolve(0);
//        dump($resolved);
        return $resolved->handle($request);
    }

    private function resolve($index): RequestHandlerInterface
    {
//        dump('Resolving index: ', $index, $this->stack[$index] ?? 'none');
//        dump($this->stack);
        if (isset($this->stack[$index])) {
            return new class(function (ServerRequestInterface $request) use ($index) {
                //Резолвер еще добавить
                $middleware = $this->stack[$index];
                if ($middleware instanceof MiddlewareInterface) {
                    return $this->stack[$index]->process($request, $this->resolve($index + 1));
                }
                if (is_callable($middleware)) {
                    return $middleware($request, $this->resolve($index + 1));
                }
            }) implements RequestHandlerInterface {
                public function __construct(private \Closure $callback)
                {
                }

                public function handle(ServerRequestInterface $request): ResponseInterface
                {
                    return ($this->callback)($request);
                }
            };
        }
        throw new LogicException("unresolved request: middleware stack exhausted with no result");
    }

    public function addMiddleware(callable|MiddlewareInterface|string $middleware): void
    {
        $this->stack[] = $middleware;
    }

    public function setMiddlewares(callable|MiddlewareInterface|array|string $middlewares): void
    {
        $this->stack = $middlewares;
    }
}