<?php

use App\Blog\Http\Controllers\HomeController;
use App\Core\Container\Container;
//use App\Core\Http\Middleware\Dispatcher;
use App\Core\Http\Exception\ExceptionHandler;
use App\Core\Http\Middleware\MiddlewareDispatcher;
use App\Core\Routes\ControllerDispatcher;
use App\Core\Routes\Router;

include __DIR__ . '/../vendor/autoload.php';

    $app = new \App\Core\Application\Application();
    $request = new \App\Core\Http\Request();
    $app->handleRequest($request);



//
//$container = new Container();
//
//$container->bind(\App\Core\Factories\RouteFactoryInterface::class, \App\Core\Factories\RouteFactory::class);
//$container->bind(\App\Core\Routes\RouteCollectionInterface::class, \App\Core\Routes\RouteCollection::class);
//$container->bind(\App\Core\Http\RequestInterface::class, \App\Core\Http\Request::class);
//$container->bind(\App\Core\Routes\ControllerDispatcherInterface::class, \App\Core\Routes\ControllerDispatcher::class);
//$container->bind(\Psr\Container\ContainerInterface::class, $container);//А вот вам и синглтон локатор
//
//$request = $container->make(\App\Core\Http\RequestInterface::class);
//$router = $container->make(Router::class);
//
//$router->get('/', [HomeController::class, 'index']);
//$router->get('/{id}', [HomeController::class, 'show']);
////$router->get('/{id}', [HomeController::class, 'show']);
////$router->get('/{id}', [\App\Blog\Http\Controllers\Controller::class]);
////$router->get('/{id}', function ($id) {
////    dd('ну ляпота');
////});
//$stack = [
//    new \App\Core\Http\Middleware\testMiddleware\Middleware1(),
//    new \App\Core\Http\Middleware\testMiddleware\Middleware2(),
//];
//
//
////preg_match($pattern, $url, $matches);
////dd($matches);
////dd(is_callable(new \App\Blog\Http\Controllers\Controller()));
////dd($router->getRoutes());
//
//
////$app = new \App\Core\Application\Application();
////
//$controllerDispatcher = $container->make(ControllerDispatcher::class);
////
//$globalMiddlewareDispatcher = new \App\Core\Http\Middleware\MiddlewareDispatcher($stack);///Удалить потом
//$routeRunner = new \App\Core\Routes\RouteDispatcher($router,new MiddlewareDispatcher());
//
//
//$newStack = [
//    $globalMiddlewareDispatcher,
//    $routeRunner,
//    $controllerDispatcher
//];
//
//$dispatcher = new MiddlewareDispatcher($newStack);
//
//$dispatcher->handle($request);
////
////$routeRunner = new \App\Core\Routes\RouteDispatcher($router, $controllerDispatcher);
////$routeMiddlewareDispatcher = new \App\Core\Middleware\MiddlewareDispatcher($routeRunner);
////
////$response =  $app->handle(new \App\Core\Http\Request());
//
//
////dd($response);
////dd($router);