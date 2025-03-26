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
use App\Core\Http\RequestInterface;
use App\Core\Http\ResponseInterface;
use App\Core\Logger\Handlers\LogHandlerInterface;
use App\Core\Logger\Handlers\StreamLogHandler;
use App\Core\Logger\Logger;
use App\Core\Routes\ControllerDispatcher;
use App\Core\Routes\Router;
use Latte\Engine;
use Latte\Loaders\FileLoader;
use Psr\Cache\CacheItemPoolInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
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
        $this->bind(\App\Core\Routes\RouteCollectionInterface::class, \App\Core\Routes\RouteCollection::class);
        $this->bind(\App\Core\Http\RequestInterface::class, \App\Core\Http\Request::class);
        $this->bind(\App\Core\Routes\ControllerDispatcherInterface::class, \App\Core\Routes\ControllerDispatcher::class);
        $this->bind(\Psr\Container\ContainerInterface::class, $this);//А вот вам и синглтон локатор
        $this->bind(ListenerProviderInterface::class, ListenerProviderComposite::class);
        $this->bind(LogHandlerInterface::class, $this->makeWith(StreamLogHandler::class, ['path' => $this->config->get('logs.default.path')]));
        $this->bind(ListenerProviderInterface::class, $this->makeWith(ListenerProviderComposite::class, ['providers' => $this->config->get('events')]));
        $this->bind(EventDispatcherInterface::class, $this->make(EventDispatcher::class));
        $this->bind(LoggerInterface::class, Logger::class);
        $this->bind(CacheItemPoolInterface::class, RedisCache::class);
        $this->bind(Redis::class, function (): Redis {
            $connector = new RedisConnector($this->config->get('redis'));
            return $connector->connection();
        });
        $this->bind(Engine::class, function (): Engine {
            $latte = new Engine();
            $latte->setTempDirectory(getcwd() . '/cache'); // Папка для кэша
            $latte->setLoader(new FileLoader('/var/www/blog/templates')); // Папка с шаблонами
            return $latte;
        });
    }

    protected function configureRoutes(Router $router): void
    {
        $router->get('/', [HomeController::class, 'index']);
        $router->get('/{id}', [HomeController::class, 'show']);
        $router->post('/{id}', [HomeController::class, 'store']);
//        $router->get('/{id}', function ($id) {
//            dd('ну ляпота');
//        });
    }

    public function handleRequest(RequestInterface $request): ResponseInterface
    {
        try {
            $this->configureContainer();
            $router = $this->make(Router::class);
            $this->configureRoutes($router);


//            $cache = new ArrayCache();
            $cache = $this->make(CacheItemPoolInterface::class);

            $item = $cache->getItem('hui6')->expiresAt(new \DateTime('2025-03-21 10:00:00'))->set('huitenb3');

            $cache->save($item);

            $this->setGlobalMiddlewares([
                new NotFoundErrorMiddleware(),
            ]);

            $controllerDispatcher = $this->make(ControllerDispatcher::class);
            $routeRunner = new \App\Core\Routes\RouteDispatcher($router, new MiddlewareDispatcher());
            $dispatcher = new MiddlewareDispatcher();
            $dispatcher->setMiddlewares($this->middlewares);
            $dispatcher->addMiddleware([
                    $routeRunner,
                    $controllerDispatcher
                ]
            );

            return $dispatcher->handle($request);
        } catch (\Exception $e) {

            return $this->handleException($e);//TODO: setCustomHandler///Вообще через ивент все сделать
        }
    }

    private function handleException(\Throwable $e): ResponseInterface
    {
        return (new ExceptionHandler())->handle($e);
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

    public function config(string $key = ''): array|string
    {
        return $this->config->get($key);
    }

    public function setGlobalMiddlewares(array $middlewares): void
    {
        $this->middlewares = $middlewares;
    }
}