<?php

namespace App\Core\Routing\Controller;

use App\Core\Container\Exceptions\NotFoundContainerException;
use App\Core\Container\Resolvers\ParametersResolverInterface;
use InvalidArgumentException;

final class ControllerResolver
{
    //TODO чтобы обойти неприятную ситуацию с resolveParameters - можно добавить интерфейс ParameterResolver в контейнер и юзать здесь именно его
    public function __construct(public ParametersResolverInterface $parametersResolver)
    {
    }

    public function resolveController(mixed $controller): callable
    {
//        if ($controller instanceof \Closure) {//Зачем я это сделал
//            return $controller;
//        }

        if (is_callable($controller)) {
            return $controller;
        }

        if (is_array($controller)) {
            $definition = $controller[0];

            try {
                $instance = $this->parametersResolver->get($definition);
            } catch (NotFoundContainerException $e) {//????
                throw new NotFoundContainerException("Controller {$definition} does not exist.");
            }

            if (is_callable($instance)) {
                return [$instance, '__invoke'];
            }

            $method = $controller[1];
            if (!is_callable([$instance, $method])) {
                throw new \BadMethodCallException("Controller {$definition} does not have method {$method} or method is not public.");
            }

            return [$instance, $method];
        }

        throw new InvalidArgumentException("Controller {$controller} is not a callable.");
    }

    public function resolveParameters(callable $controller, array $parameters): array
    {
        if (is_array($controller)) {
            $reflector = new \ReflectionMethod($controller[0], $controller[1]);
        }

        if ($controller instanceof \Closure) {
            $reflector = new \ReflectionFunction($controller);
        }

        $methodParameters = $reflector->getParameters();

        return $this->parametersResolver->resolveParameters($methodParameters, 'controller', $parameters);
    }
}