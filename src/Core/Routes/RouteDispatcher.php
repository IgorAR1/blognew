<?php

namespace App\Core\Routes;

use App\Core\Http\Middleware\MiddlewareDispatcherInterface;
use App\Core\Http\Middleware\MiddlewareInterface;
use App\Core\Http\Middleware\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouteDispatcher implements MiddlewareInterface///Это что то типа прокси
{
    //Этот дистпатчер будет вызываться внутри другого диспатчера))

    // То есть этот класс - fallback handler внутри основного диспатчера
    // а контроллер диспатчер - fallback handler внутри этого диспатчера
    // так я соединю потоки глобальных мидлваров и локальных для роута например
    //private RequestHandlerInterface $fallbackHandler;///ControllerDispatcher!!!!

    public function __construct(readonly Router $router,///На интерфейс заменить
                                readonly MiddlewareDispatcherInterface $middlewareDispatcher)
    {
    }
    ///Есть ощущение что диспатчер должен роут вызывать o_0
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->router->findRoute($request);
        $route->bindParameters($request);

        $request = $request->withAttribute('_controller' , $route->getController());
        $request = $request->withAttribute('_parameters' , $route->getParameters());

        $test = [
//            new \App\Core\Http\Middleware\testMiddleware\Middleware1(),
//            new \App\Core\Http\Middleware\testMiddleware\Middleware2(),
        ];

        $this->middlewareDispatcher->setMiddlewares($test);

        return $this->middlewareDispatcher->process($request, $handler);
    }
}