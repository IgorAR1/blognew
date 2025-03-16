<?php

namespace App\Core\Routes;

use App\Core\Container\Exceptions\NotFoundContainerException;
use App\Core\Http\Controllers\ControllerInterface;
use Psr\Container\ContainerInterface;

final class ControllerDispatcher implements ControllerDispatcherInterface
{
    public function __construct(readonly ContainerInterface $container)
    {
    }

    public function dispatch(mixed $controller, array $parameters)//ResponseInterface
    {
        $controller = $this->resolveController($controller);

        $parameters = $this->resolveParameters($controller, $parameters);

        return $this->runController($controller, $parameters);
    }

    private function resolveController(mixed $controller): callable
    {
        if ($controller instanceof \Closure) {
            return $controller;
        }

        if (is_array($controller)) {
            try {
                $instance = $this->container->make($controller[0]);
            } catch (NotFoundContainerException $e) {
                throw new RoutingException("Controller {$controller[0]} does not exist.");
            }

            if (is_callable($instance)) {
                return [$instance, '_invoke'];
            }

            return [$instance, $controller[1]];
        }
        //TODO: ошибку поменять
        throw new RoutingException("Controller {$controller} is not a callable.");
//        return $controller;
    }

    //TODO: конечно этот резолв по любому должен уехать в отдельный резолвер, а тут вызываться уже стек резолверов
    private function resolveParameters(callable $controller, array $parameters): array
    {
        if (is_array($controller)){
            $reflector = new \ReflectionMethod($controller[0], $controller[1]);
        }

        if ($controller instanceof \Closure) {
            $reflector = new \ReflectionFunction($controller);
        }

//        if (is_object($controller)){
//            $reflector = new \ReflectionMethod($controller, '__invoke');
//        }

//        try {
//            $method = new \ReflectionMethod($controller);
//        } catch (\ReflectionException $exception) {
//            throw new RoutingException("Action {$controller} does not exist.");
//        }

        $controllerParameters = $reflector->getParameters();

        $resolvedParameters = [];

        foreach ($controllerParameters as $methodParameter) {
            $parameterName = $methodParameter->getName();

            if (array_key_exists($parameterName, $parameters)) {
                $resolvedParameters[] = $parameters[$parameterName];

                continue;
            }

            if ($methodParameter->isOptional()) {
                if ($methodParameter->isDefaultValueAvailable()) {
                    $resolvedParameters[] = $methodParameter->getDefaultValue();
                }

                continue;
            }

            if (!$methodParameter->hasType()) {
                throw new \ArgumentCountError("Missing required parameter {$parameterName} in {$reflector->getName()} method");
            }

            $parameterTypeName = $methodParameter->getType()->getName();

            if (!$this->container->has($parameterTypeName)) {
                throw new \ArgumentCountError("Missing required parameter {$parameterName} of type {$parameterTypeName} in {$reflector->getName()} method");
            }

            $resolvedParameters[] = $this->container->make($parameterTypeName);
        }

        return $resolvedParameters;
    }


    private function runController(callable $controller, array $parameters)//ResponseInterface
    {
        return $controller(...$parameters);
    }

}