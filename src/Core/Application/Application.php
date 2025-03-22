<?php

namespace App\Core\Application;

use App\Blog\Http\Controllers\HomeController;
use App\Core\Config\Config;
use App\Core\Container\Container;
use App\Core\Http\Exception\ExceptionHandler;
use App\Core\Http\Middleware\MiddlewareDispatcher;
use App\Core\Http\RequestInterface;
use App\Core\Http\ResponseInterface;
use App\Core\Logger\Handlers\LogHandlerInterface;
use App\Core\Logger\Handlers\StreamLogHandler;
use App\Core\Logger\AbstractLogger;
use App\Core\Logger\Logger;
use App\Core\Routes\ControllerDispatcher;
use App\Core\Routes\Router;
use Psr\Log\LoggerInterface;

class Application extends Container
{
    private string $basePath;
    private string $configPath;

    private array $middlewares = [];
    private Config $config;

    public function __construct()
    {
        $this->prepareConfig();
    }

    public function handleRequest(RequestInterface $request): ResponseInterface
    {
        try {

//            dd(parse_ini_file('/var/www/blog/.env'));
            $this->bind(\App\Core\Factories\RouteFactoryInterface::class, \App\Core\Factories\RouteFactory::class);
            $this->bind(\App\Core\Routes\RouteCollectionInterface::class, \App\Core\Routes\RouteCollection::class);
            $this->bind(\App\Core\Http\RequestInterface::class, \App\Core\Http\Request::class);
            $this->bind(\App\Core\Routes\ControllerDispatcherInterface::class, \App\Core\Routes\ControllerDispatcher::class);
            $this->bind(\Psr\Container\ContainerInterface::class, $this);//А вот вам и синглтон локатор

            $this->bind(LogHandlerInterface::class, $this->makeWith(StreamLogHandler::class, ['path' => $this->config->get('logs.default.path')]));

//            dd($this->config('logs.default.path'));
            $this->bind(LoggerInterface::class, Logger::class);
//            $this->bind(LogHandlerInterface::class,StreamLogHandler::class);
            $logger = $this->make(LoggerInterface::class);

//            $logger->alert('ddd');
            $router = $this->make(Router::class);

            $router->get('/', [HomeController::class, 'index']);
            $router->get('/{id}', [HomeController::class, 'show']);
//$router->get('/{id}', [HomeController::class, 'show']);
//$router->get('/{id}', [\App\Blog\Http\Controllers\Controller::class]);
//$router->get('/{id}', function ($id) {
//    dd('ну ляпота');
//});
            $stack = [
                new \App\Core\Http\Middleware\testMiddleware\Middleware1(),
                new \App\Core\Http\Middleware\testMiddleware\Middleware2(),
            ];

//            $dispatcher = new MiddlewareDispatcher();



            $this->setGlobalMiddlewares([
                new \App\Core\Http\Middleware\testMiddleware\Middleware1(),
                new \App\Core\Http\Middleware\testMiddleware\Middleware2(),
                ]);

            $controllerDispatcher = $this->make(ControllerDispatcher::class);

//            $globalMiddlewareDispatcher = new \App\Core\Http\Middleware\MiddlewareDispatcher($stack);///Удалить потом
            $routeRunner = new \App\Core\Routes\RouteDispatcher($router, new MiddlewareDispatcher());

//            $newStack = [
//                $globalMiddlewareDispatcher,
//                $routeRunner,
//                $controllerDispatcher
//            ];
//
//            $dispatcher = new MiddlewareDispatcher($newStack);
//
            $dispatcher = new MiddlewareDispatcher();

            $dispatcher->setMiddlewares($this->middlewares);

            $dispatcher->addMiddleware([
                    $routeRunner,
                    $controllerDispatcher
                ]
            );


            return $dispatcher->handle($request);
        } catch (\Exception $exception) {
            (new ExceptionHandler($logger))->handle($exception);//TODO: setCustomHandler
        }
    }


    private function prepareConfig(): void
    {
        $this->configPath = "/var/www/blog/src"; //TODO: тут хардкод пока

        $parameters = [];

        $files = glob($this->configPath . '/**/configs/*.php', GLOB_BRACE);

        foreach ($files as $file) {
            $name = explode('/', $file);
            $name = end($name);
            $name = str_replace('.php', '', $name);

//            if (str_ends_with($name, 'cache')) {
//                continue;
//            }

            $parameters[$name] = include $file;
        }

        $this->config = new Config($parameters);
    }

    public function config(string $key = ''): array|string
    {
        return $this->config->get($key);
    }

    public function setGlobalMiddlewares(array $middlewares): void
    {
        $this->middlewares = $middlewares;
    }
//
//    public function handle(RequestInterface $request): ResponseInterface
//    {
//        return $this->fallbackHandler->handle($request);
//    }
}