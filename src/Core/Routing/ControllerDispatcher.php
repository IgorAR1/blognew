<?php

namespace App\Core\Routing;

use App\Core\Container\Exceptions\NotFoundContainerException;
use App\Core\Http\Middleware\MiddlewareInterface;
use App\Core\Http\Middleware\RequestHandlerInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TypeError;

final class ControllerDispatcher implements ControllerDispatcherInterface
{
    public function __construct(readonly ContainerInterface $container)
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
        $controller = $this->resolveController($controller);

        $parameters = $this->resolveParameters($controller, $parameters);


        return $this->runController($controller, $parameters);

    }

    private function resolveController(mixed $controller): callable
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
                $instance = $this->container->make($definition);
            } catch (NotFoundContainerException $e) {//????
                throw new NotFoundContainerException("Controller {$definition} does not exist.");
            }

            if (is_callable($instance)) {
                return [$instance, '__invoke'];
            }

            $method = $controller[1];
            if (!method_exists($instance, $method)) {
                throw new \BadMethodCallException("Controller {$definition} does not have method {$method}.");
            }

            return [$instance, $method];
        }
        //TODO: ошибку поменять
        throw new InvalidArgumentException("Controller {$controller} is not a callable.");
    }

    //TODO: конечно этот резолв по любому должен уехать в отдельный резолвер, а тут вызываться уже стек резолверов
    private function resolveParameters(callable $controller, array $parameters): array
    {
        if (is_array($controller)) {
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