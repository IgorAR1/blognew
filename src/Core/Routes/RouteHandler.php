<?php

namespace App\Core\Routes;

use App\Core\Http\RequestInterface;
use App\Core\Http\ResponseInterface;
use App\Core\Middleware\RequestHandlerInterface;

use Psr\Http\Message\ServerRequestInterface;

class RouteHandler implements RequestHandlerInterface
{
    public function __construct(readonly ControllerDispatcherInterface $controllerDispatcher,
                                readonly Router $router)///На интерфейс заменить
    {
    }

    public function handle(RequestInterface $request): ResponseInterface
    {
        $route = $this->router->findRoute($request);
        $route->bindParameters($request);

        $response = $this->controllerDispatcher->dispatch($route->getController(), $route->getParameters());

        return $response;
    }
}