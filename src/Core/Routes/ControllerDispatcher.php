<?php

namespace App\Core\Routes;

use App\Core\Container\Exceptions\NotFoundContainerException;
use App\Core\Http\Controllers\ControllerInterface;
use Psr\Container\ContainerInterface;

class ControllerDispatcher implements ControllerDispatcherInterface
{

    public function __construct(readonly ContainerInterface $container)
    {}

    public function dispatch(string $controllerDefinition, string $action, array $parameters)//ResponseInterface
    {
        $controller = $this->resolveController($controllerDefinition, $action);

        $parameters = $this->resolveParameters($controllerDefinition, $action, $parameters);

        return $this->runController($controller, $action, $parameters);
    }

    private function resolveController(string $controllerDefinition, string $action): ControllerInterface
    {
        try {
            $controller = $this->container->make($controllerDefinition);
        } catch (NotFoundContainerException $e) {//NotFoundException!!!!!!!!!!!
            throw new RoutingException("Controller {$controllerDefinition} does not exist.");
        }

        return $controller;
    }

    private function resolveParameters(string $controllerDefinition, string $action, array $parameters): array
    {
        try {
            $method = new \ReflectionMethod($controllerDefinition, $action);
        } catch (\ReflectionException $exception) {
            throw new RoutingException("Action {$controllerDefinition} does not exist.");
        }

        $controllerParameters = $method->getParameters();

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
                throw new \ArgumentCountError("Missing required parameter {$parameterName} in {$action} method");
            }

            $parameterTypeName = $methodParameter->getType()->getName();

            if (!$this->container->has($parameterTypeName)) {
                throw new \ArgumentCountError("Missing required parameter {$parameterName} of type {$parameterTypeName} in {$action} method");
            }

            $resolvedParameters[] = $this->container->make($parameterTypeName);
        }

        return $resolvedParameters;
    }


    private function runController(ControllerInterface $controller, string $action, array $parameters)//ResponseInterface
    {
        return $controller->{$action}(...$parameters);
    }

}