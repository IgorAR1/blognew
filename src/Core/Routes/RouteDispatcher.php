<?php

namespace App\Core\Routes;

use App\Core\Http\Middleware\MiddlewareDispatcher;
use App\Core\Http\Middleware\MiddlewareDispatcherInterface;
use App\Core\Http\Middleware\MiddlewareInterface;
use App\Core\Http\Middleware\RequestHandlerInterface;
use App\Core\Http\RequestInterface;
use App\Core\Http\ResponseInterface;

class RouteDispatcher implements MiddlewareInterface///Это что то типа прокси
{
    //Этот дистпатчер будет вызываться внутри другого диспатчера))

    // То есть этот класс - fallback handler внутри основного диспатчера
    // а контроллер диспатчер - fallback handler внутри этого диспатчера
    // так я соединю потоки глобальных мидлваров и локальных для роута например
    //private RequestHandlerInterface $fallbackHandler;///ControllerDispatcher!!!!
    private array $stack = [];


    public function __construct(readonly Router $router,///На интерфейс заменить
                                readonly MiddlewareDispatcherInterface $middlewareDispatcher)
    {
    }
    ///Есть ощущение что диспатчер должен роут вызывать o_0
    public function process(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $route = $this->router->findRoute($request);

        $route->bindParameters($request);

        $request->withAttribute(['_controller' => $route->getController(),'_parameters' => $route->getParameters()]);

        $test = [
            new \App\Core\Http\Middleware\testMiddleware\Middleware1(),
            new \App\Core\Http\Middleware\testMiddleware\Middleware2(),
        ];
//        $this->middlewareDispatcher->addMiddleware($route->getMiddleware());
        $this->middlewareDispatcher->setMiddlewares($test);

//        $request->setController($route->getController());
//        $request->setParameters($route->getParameters());


        $this->stack[] = $handler;

        return $this->middlewareDispatcher->process($request, $handler);

//
//        return $this->handle($request);
    }

//    public function handle(RequestInterface $request): ResponseInterface
//    {
//
//        $route = $this->router->findRoute($request);
//        $route->bindParameters($request);
//
//        $test = [
//            new \App\Core\Http\Middleware\testMiddleware\Middleware1(),
//            new \App\Core\Http\Middleware\testMiddleware\Middleware2(),
//        ];
////        $this->middlewareDispatcher->addMiddleware($route->getMiddleware());
//        $args = array_merge($test, $this->stack);
//        $this->middlewareDispatcher->setMiddlewares($args);
//
////        $request->setController($route->getController());
////        $request->setParameters($route->getParameters());
//
//        return $this->middlewareDispatcher->handle($request);
//
//        ///Потом запускаем диспатчер
////        $request->setController($route->getController());
////        $request->setParameters($route->getParameters());
//        ////
////        $response = $this->controllerDispatcher->dispatch($route->getController(), $route->getParameters());
//
////        return $response;
//    }
}