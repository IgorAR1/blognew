<?php

namespace App\Core\Routing;

use App\Core\Support\ServiceProvider\ServiceProvider;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

class RouteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->application->bind(\App\Core\Routing\RouteFactoryInterface::class, \App\Core\Routing\RouteFactory::class);
        $this->application->bind(\App\Core\Routing\RouteCollectionInterface::class, \App\Core\Routing\RouteCollection::class);
        $this->application->bind(ServerRequestInterface::class, function (): ServerRequestInterface {
            return ServerRequest::fromGlobals();
        });
        $this->application->bind(\App\Core\Routing\Controller\ControllerDispatcherInterface::class, \App\Core\Routing\Controller\ControllerDispatcher::class);
    }
}