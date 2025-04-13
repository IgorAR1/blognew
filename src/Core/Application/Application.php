<?php

namespace App\Core\Application;

use App\Core\Cache\Redis\RedisCache;
use App\Core\Cache\Redis\RedisConnector;
use App\Core\Config\Config;
use App\Core\Container\Container;
use App\Core\Container\Resolvers\ParametersResolverInterface;
use App\Core\Event\EventDispatcher;
use App\Core\Event\ListenerProviderComposite;
use App\Core\Exception\ErrorHandler;
use App\Core\Http\Middleware\NotFoundErrorMiddleware;
use App\Core\Kernel\HttpKernel;
use App\Core\Logger\Handlers\LogHandlerInterface;
use App\Core\Logger\Handlers\StreamLogHandler;
use App\Core\Logger\Logger;
use App\Core\Routing\RouteCollectionInterface;
use App\Core\Routing\RouteFactoryInterface;
use App\Core\Routing\Router;
use App\Core\Routing\RoutesRegistrar;
use GuzzleHttp\Psr7\ServerRequest;
use Latte\Engine;
use Latte\Loaders\FileLoader;
use Psr\Cache\CacheItemPoolInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Redis;

class Application extends Container implements ApplicationInterface
{
    private string $basePath;//
    private string $configPath;
    private array $middlewares = [];
    private array $serviceProviders = [];
    private Config $config;

    public function __construct()
    {
        $this->registerBaseBind();
        $this->prepareConfig();

    }

    protected function registerBaseBind(): void
    {
        $this->bind(\Psr\Container\ContainerInterface::class, $this);
        $this->bind(ParametersResolverInterface::class, $this);
        $this->bind(Engine::class, function (): Engine {
            $latte = new Engine();
            $latte->setTempDirectory(getcwd() . '/cache');
            $latte->setLoader(new FileLoader('/var/www/blog/templates'));
            return $latte;
        });
    }

    public function handleRequest(ServerRequestInterface $request): void
    {
        $this->registerProviders();

        $kernel = $this->make(HttpKernel::class);
        $kernel->setGlobalMiddlewares($this->middlewares);

        $kernel->handle($request);
    }

    private function prepareConfig(): void
    {
        $this->configPath = "/var/www/blog/src";

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

    private function registerProviders(): void
    {
        foreach ($this->serviceProviders as $serviceProvider) {
            $serviceProvider = $this->makeWith($serviceProvider, ['application' => $this]);

            $serviceProvider->register($this);
        }
    }

    public function withProviders(array $providers): static
    {
        $this->serviceProviders = $providers;
        return $this;
    }

    public function withMiddleware(array $middlewares): static
    {
        $this->middlewares = $middlewares;

        return $this;
    }
}