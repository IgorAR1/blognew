<?php

namespace App\Core\Kernel;

use App\Core\Exception\ErrorHandler;
use App\Core\Http\Middleware\MiddlewareDispatcher;
use App\Core\Routing\Controller\ControllerDispatcher;
use App\Core\Routing\RouteDispatcher;
use App\Core\Routing\Router;
use App\Core\Routing\RoutesRegistrar;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpKernel
{
    private array $middlewares;

    public function __construct(
        private ErrorHandler       $errorHandler,
        private ContainerInterface $container)
    {

    }

    public function handle(ServerRequestInterface $request): void
    {
        try {
            $router = $this->container->make(Router::class);
            $routeRegistrar = new RoutesRegistrar($router);
            $routeRegistrar->registerRoutes();

            $controllerDispatcher = $this->container->get(ControllerDispatcher::class);

            $routeDispatcher = new RouteDispatcher($router, new MiddlewareDispatcher($this->container));

            $dispatcher = new MiddlewareDispatcher($this->container);
            $dispatcher->setMiddlewares($this->middlewares);
            $dispatcher->addMiddleware([
                    $routeDispatcher,
                    $controllerDispatcher
                ]
            );

            $response = $dispatcher->handle($request);

            $this->sendResponse($response);
        } catch (\Throwable $e) {
            $this->errorHandler->handle($e);//Логгировать лучше через эвенты
        }
    }

    private function sendResponse(ResponseInterface $response): void
    {
        http_response_code($response->getStatusCode());

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value));
            }
        }

        echo $response->getBody()->getContents();
    }

    public function setGlobalMiddlewares(array $middlewares): void
    {
        $this->middlewares = $middlewares;
    }
}