<?php

use App\Container\Container;
use App\Http\Controllers\HomeController;
use App\Routes\Router;

include __DIR__ . '/../vendor/autoload.php';

//
$container = new Container();
$container->bind(\App\Factories\RouteFactoryInterface::class, \App\Factories\RouteFactory::class);

$router = $container->make(Router::class);
$router->get('/', HomeController::class, 'index');
$router->get('/{value}', HomeController::class, 'show');

//dd($router->getRoutes());
return $router->dispatch(new \App\Http\Request());
//dd($router);