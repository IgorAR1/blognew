<?php

namespace App\Core\Routing;

use App\Core\Http\Middleware\MiddlewareDispatcherInterface;
use App\Core\Http\Middleware\MiddlewareInterface;
use App\Core\Http\Middleware\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouteDispatcher implements MiddlewareInterface
{
    public function __construct(readonly Router $router,
                                readonly MiddlewareDispatcherInterface $middlewareDispatcher)
    {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->router->findRoute($request);
        $route->bindParameters($request);

        $request = $request->withAttribute('_controller' , $route->getController());
        $request = $request->withAttribute('_parameters' , $route->getParameters());

        $middleware = $route->getMiddleware();

        $this->middlewareDispatcher->setMiddlewares($middleware);

        return $this->middlewareDispatcher->process($request, $handler);
    }
}