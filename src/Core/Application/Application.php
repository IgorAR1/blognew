<?php

namespace App\Core\Application;

use App\Blog\Http\Controllers\HomeController;
use App\Core\Cache\Redis\RedisCache;
use App\Core\Cache\Redis\RedisConnector;
use App\Core\Config\Config;
use App\Core\Container\Container;
use App\Core\Event\EventDispatcher;
use App\Core\Event\ListenerProviderComposite;
use App\Core\Http\Exception\ExceptionHandler;
use App\Core\Http\Middleware\MiddlewareDispatcher;
use App\Core\Http\Middleware\NotFoundErrorMiddleware;
use App\Core\Logger\Handlers\LogHandlerInterface;
use App\Core\Logger\Handlers\StreamLogHandler;
use App\Core\Logger\Logger;
use App\Core\Routing\ControllerDispatcher;
use App\Core\Routing\Router;
use App\Core\Routing\RoutesRegistrar;
use GuzzleHttp\Psr7\ServerRequest;
use Latte\Engine;
use Latte\Loaders\FileLoader;
use Psr\Cache\CacheItemPoolInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Redis;

/////////////////////////////////////////////////////////
/// //TODO: мне кажется что все что связанно с контейнеризацией
///  и конфигурацие остается здесь - остальное летит в ядро Kernel а тут мы тупо резолвим в общем рил Composition root
/// Kernel
///   public function __construct(Application $app, Router $router)
//    {
//        $this->app = $app;
//        $this->router = $router;
//
//        $this->syncMiddlewareToRouter();
//    }
class Application extends Container
{
    private string $basePath;
    private string $configPath;
    private array $middlewares = [];
    private Config $config;

    public function __construct()
    {
        $this->prepareConfig();//???
    }

    protected function configureContainer()
    {
        $this->bind(\App\Core\Factories\RouteFactoryInterface::class, \App\Core\Factories\RouteFactory::class);
        $this->bind(\App\Core\Routing\RouteCollectionInterface::class, \App\Core\Routing\RouteCollection::class);
        $this->bind(ServerRequestInterface::class, function (): ServerRequestInterface {
                return ServerRequest::fromGlobals();
        });
        $this->bind(\App\Core\Routing\ControllerDispatcherInterface::class, \App\Core\Routing\ControllerDispatcher::class);
        $this->bind(\Psr\Container\ContainerInterface::class, $this);//А вот вам и синглтон локатор
        $this->bind(ListenerProviderInterface::class, ListenerProviderComposite::class);
        $this->bind(LogHandlerInterface::class, fn() =>  $this->makeWith(StreamLogHandler::class, ['path' => $this->config->get('logs.default.path')]));
        $this->bind(ListenerProviderInterface::class, fn() =>  $this->makeWith(ListenerProviderComposite::class, ['providers' => $this->config->get('events')]));
        $this->bind(EventDispatcherInterface::class, fn() => $this->make(EventDispatcher::class));
        $this->bind(LoggerInterface::class, Logger::class);
        $this->bind(CacheItemPoolInterface::class, RedisCache::class);
        $this->bind(Redis::class, function (): Redis {
            $connector = new RedisConnector($this->config->get('redis'));
            return $connector->connection();
        });
        $this->bind(Engine::class, function (): Engine {
            $latte = new Engine();
            $latte->setTempDirectory(getcwd() . '/cache');
            $latte->setLoader(new FileLoader('/var/www/blog/templates'));
            return $latte;
        });
    }

    protected function configureRoutes(Router $router): void
    {
        $router->get('/', [HomeController::class, 'index']);
        $router->get('/{id}', [HomeController::class, 'show']);
        $router->post('/{id}', [HomeController::class, 'store']);
    }

    public function handleRequest(ServerRequestInterface $request): void
    {
        try {
            $this->configureContainer();

            $router = $this->make(Router::class);
            $routeRegistrar = new RoutesRegistrar($router);

            $routeRegistrar->registerRoutes();
//            $this->configureRoutes($routeRegistrar);

            $cache = $this->make(CacheItemPoolInterface::class);

            $this->setGlobalMiddlewares([
                new NotFoundErrorMiddleware(),
            ]);

            $controllerDispatcher = $this->make(ControllerDispatcher::class);
            $routeRunner = new \App\Core\Routing\RouteDispatcher($router, new MiddlewareDispatcher($this));
            $dispatcher = new MiddlewareDispatcher($this);
            $dispatcher->setMiddlewares($this->middlewares);
            $dispatcher->addMiddleware([
                    $routeRunner,
                    $controllerDispatcher
                ]
            );

            $response =  $dispatcher->handle($request);

            $this->sendResponse($response);

//            return $response;
        } catch (\Exception $e) {
            $this->handleException($e);//TODO: setCustomHandler///Вообще через ивент все сделать
        }
    }

    private function handleException(\Throwable $e): void
    {
        (new ExceptionHandler())->handle($e);
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
            $parameters[$name] = include $file;
        }

        $this->config = new Config($parameters);
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

    public function config(string $key = ''): array|string
    {
        return $this->config->get($key);
    }

    public function setGlobalMiddlewares(array $middlewares): void
    {
        $this->middlewares = $middlewares;
    }
}