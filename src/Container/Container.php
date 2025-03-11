<?php

namespace App\Container;

use App\Container\Exceptions\ContainerException;
use App\Container\Exceptions\NotFoundException;
use App\ExampleClass2;
use Psr\Container\ContainerInterface;
use ReflectionClass;

class Container implements ContainerInterface
{

    /**
     * @var array<object>
     */
    private array $resolvedInstances = [];
    private array $binds;
    private array $inProgress;

    public function __construct(array $binds = [])
    {
        $this->binds = $binds;

    }

    /**
     * @throws \ReflectionException
     */
    public function get(string $id): object
    {
        if (array_key_exists($id, $this->resolvedInstances)) {
            return $this->resolvedInstances[$id];
        }

        $instance = $this->make($id);
        $this->resolvedInstances[$id] = $instance;

        return $instance;
    }

    public function resolve(string $definition)
    {

    }

    /**
     * @throws \ReflectionException
     */
    public function make(string $definition): object
    {
        if (!$this->isInstantiable($definition)) {
            throw new NotFoundException($definition .' is not instantiable');
        }

        if (isset($this->inProgress[$definition])) {
            throw new ContainerException('Cyclic dependency resolved instance is already in container');
        }

        $this->inProgress[$definition] = true;

        if (array_key_exists($definition, $this->binds)) {
            $definition = $this->binds[$definition];
        }

        $reflector = new ReflectionClass($definition);

        $resolvedParameters = [];
        /////Это можно выкинуть в отдельный резолвер
        if ($parameters = $reflector->getConstructor()) {
            foreach ($parameters->getParameters() as $parameter) {
                $className = $parameter->getType()->getName();
                $resolvedParameters[] = $this->make($className);
            }
        }
        /////////////////////////////////////////////////
        unset($this->inProgress[$definition]);

//        return $reflector->newInstanceArgs($resolvedParameters);
        return new $definition(...$resolvedParameters);
    }

    public function makeWith(string $definition, array $parameters = []): object
    {
        if (!$this->isInstantiable($definition)) {
            throw new NotFoundException($definition .' is not instantiable');
        }

        if (array_key_exists($definition, $this->binds)) {
            $definition = $this->binds[$definition];
        }

        $this->inProgress[$definition] = true;

        $reflector = new ReflectionClass($definition);

        $constructorParameters = $reflector->getConstructor()->getParameters();

        $resolvedParameters = [];

        foreach ($constructorParameters as $methodParameter) {
            $parameterName = $methodParameter->getName();
            $parameterTypeName = $methodParameter->getType()->getName();

            if (array_key_exists($parameterName, $parameters)) {
                $resolvedParameters[] = $parameters[$parameterName];

                continue;
            }
            if ($methodParameter->isOptional()) {
                continue;
            }
            if (!$this->isInstantiable($parameterTypeName)){
                throw new ContainerException("Missing required parameter {$parameterName} of type {$parameterTypeName}");
            }

            $resolvedParameters[] = $this->make($parameterTypeName);
        }

        return new $definition(...$resolvedParameters);
    }

    public function bind($abstract, $concrete): void
    {
        $this->binds[$abstract] = $concrete;
    }

    public function isInstantiable(string $definition): bool
    {
        if (array_key_exists($definition, $this->binds)){
            if (class_exists($this->binds[$definition])) {
                return true;
            }
        }

        if(class_exists($definition)){
            $reflector = new ReflectionClass($definition);
            if (!$reflector->isAbstract()) {
                return true;
            }
        }

        return false;
    }
    public function has(string $id): bool
    {
        if (array_key_exists($id, $this->resolvedInstances) || $this->isInstantiable($id)) {
            return true;
        }

        return false;
    }

}