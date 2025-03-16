<?php

use App\Blog\Http\Controllers\HomeController;
use App\Core\Container\Container;
use App\Core\Routes\Router;

include __DIR__ . '/../vendor/autoload.php';

//
$container = new Container();

$container->bind(\App\Core\Factories\RouteFactoryInterface::class, \App\Core\Factories\RouteFactory::class);
$container->bind(\App\Core\Routes\RouteCollectionInterface::class, \App\Core\Routes\RouteCollection::class);
$container->bind(\App\Core\Http\RequestInterface::class, \App\Core\Http\Request::class);
$container->bind(\App\Core\Routes\ControllerDispatcherInterface::class, \App\Core\Routes\ControllerDispatcher::class);
$container->bind(\Psr\Container\ContainerInterface::class, $container);

$request = $container->make(\App\Core\Http\RequestInterface::class);
$router = $container->make(Router::class);


$router->get('/', HomeController::class, 'index');
//$router->get('/{id}', HomeController::class, 'show');
$router->get('/{id}', HomeController::class, 'show');

//preg_match($pattern, $url, $matches);
//dd($matches);
dd(is_callable(new \App\Blog\Http\Controllers\Controller()));
//dd($router->getRoutes());
$response =  $router->dispatch(new \App\Core\Http\Request());

dd($response);
//dd($router);