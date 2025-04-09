<?php

namespace App\Core\Routing\Controller;

use App\Core\Http\Middleware\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TypeError;

final class ControllerDispatcher implements ControllerDispatcherInterface
{
//    public function __construct(readonly ContainerInterface $container)
//    {
//    }
    public function __construct(readonly ControllerResolver $resolver)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->handle($request);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface//?
    {
        $controller = $request->getAttribute('_controller');

        $parameters = $request->getAttribute('_parameters');
        $parameters['request'] = $request;

        return $this->dispatch($controller, $parameters);
    }

    public function dispatch(mixed $controller, array $parameters): ResponseInterface
    {
        $controller = $this->resolver->resolveController($controller);
        $parameters = $this->resolver->resolveParameters($controller, $parameters);

        return $this->runController($controller, $parameters);

    }

//    private function resolveController(mixed $controller): callable
//    {
////        if ($controller instanceof \Closure) {//Зачем я это сделал
////            return $controller;
////        }
//
//        if (is_callable($controller)) {
//            return $controller;
//        }
//
//        if (is_array($controller)) {
//            $definition = $controller[0];
//
//            try {
//                $instance = $this->container->get($definition);
//            } catch (NotFoundExceptionInterface $e) {//????
//                throw new NotFoundContainerException("Controller {$definition} does not exist.");
//            }
//
//            if (is_callable($instance)) {
//                return [$instance, '__invoke'];
//            }
//
//            $method = $controller[1];
//            if (!is_callable([$instance, $method])) {
//                throw new \BadMethodCallException("Controller {$definition} does not have method {$method} or method is not public.");
//            }
//
//            return [$instance, $method];
//        }
//        //TODO: ошибку поменять
//        throw new InvalidArgumentException("Controller {$controller} is not a callable.");
//    }
//
//    //TODO: конечно этот резолв по любому должен уехать в отдельный резолвер, а тут вызываться уже стек резолверов
//    private function resolveParameters(callable $controller, array $parameters): array
//    {
//        if (is_array($controller)) {
//            $reflector = new \ReflectionMethod($controller[0], $controller[1]);
//        }
//
//        if ($controller instanceof \Closure) {
//            $reflector = new \ReflectionFunction($controller);
//        }
//
////        if (is_object($controller)){
////            $reflector = new \ReflectionMethod($controller, '__invoke');
////        }
//
////        try {
////            $method = new \ReflectionMethod($controller);
////        } catch (\ReflectionException $exception) {
////            throw new RoutingException("Action {$controller} does not exist.");
////        }
//        //TODO: Обязательно подумать над классом резолвером параметров
//        $controllerParameters = $reflector->getParameters();
//
//        $resolvedParameters = [];
//
//        foreach ($controllerParameters as $methodParameter) {
//            $parameterName = $methodParameter->getName();
//
//            if (isset($parameters[$parameterName])) {
//                $resolvedParameters[] = $parameters[$parameterName];
//
//                continue;
//            }
//
//            if ($methodParameter->isOptional()) {
//                if ($methodParameter->isDefaultValueAvailable()) {
//                    $resolvedParameters[] = $methodParameter->getDefaultValue();
//                }
//
//                continue;
//            }
//
//            if (!$methodParameter->hasType()) {
//                throw new \ArgumentCountError("Missing required parameter {$parameterName} in {$reflector->getName()} method");
//            }
//
//            $parameterTypeName = $methodParameter->getType()->getName();
//
//            if (!$this->container->has($parameterTypeName)) {
//                throw new \ArgumentCountError("Missing required parameter {$parameterName} of type {$parameterTypeName} in {$reflector->getName()} method");
//            }
//
//            $resolvedParameters[] = $this->container->get($parameterTypeName);
//        }
//
//        return $resolvedParameters;
//    }

    private function runController(callable $controller, array $parameters): ResponseInterface
    {
        $response = $controller(...$parameters);

        if (!$response instanceof ResponseInterface) {
            $type = get_debug_type($response);
            throw new TypeError("Controller return value must be of type " . ResponseInterface::class . ", {$type} returned");
        }

        return $response;
    }
}