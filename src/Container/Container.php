<?php

namespace App\Container;

use App\Container\Exceptions\ContainerException;
use App\Container\Exceptions\NotFoundException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;

class Container implements ContainerInterface
{
    /**
     * @var array<object>
     */
    private array $resolvedInstances = [];
    private array $binds = [];
    private array $inProgress;
    private array $with = [];

    public function __construct()
    {
    }

    public function get(string $id): object
    {
        if (array_key_exists($id, $this->resolvedInstances)) {
            return $this->resolvedInstances[$id];
        }

        $instance = $this->make($id);

        if (!array_key_exists($id, $this->resolvedInstances)) {
            $this->resolvedInstances[$id] = $instance;
        }

        return $instance;
    }


    public function makeWith(string $definition, array $parameters = []): object
    {
        $this->notInstantiable($definition);

//        if (isset($this->inProgress[$definition])) {
//            throw new ContainerException('Cyclic dependency resolved instance is already in container');
//        }

        $this->with = $parameters;

        $this->markAsInProgress($definition);

        if (array_key_exists($definition, $this->binds)) {
            $definition = $this->binds[$definition];
        }

        $resolvedDependencies = $this->resolveParameters($definition);

        $this->unmarkAsInProgress($definition);

        return new $definition(...$resolvedDependencies);
    }


    public function make(string $definition): object
    {
        if (isset($this->inProgress[$definition])) {
            throw new ContainerException('Cyclic dependency resolved instance is already in container');
        }

        $this->notInstantiable($definition);

        if (array_key_exists($definition, $this->binds)) {
            $definition = $this->binds[$definition];
        }

        $this->markAsInProgress($definition);

        $resolvedDependencies = $this->resolveDependencies($definition);

        $this->unmarkAsInProgress($definition);

        return new $definition(...$resolvedDependencies);
//        return $reflector->newInstanceArgs($resolvedDependencies);
    }

    private function resolveDependencies(string $definition): array
    {
        $reflector = new ReflectionClass($definition);

        $dependencies = [];

        if ($reflector->hasMethod('__construct')){
            $dependencies = $reflector->getConstructor()->getParameters();
        }
        elseif(!$reflector->getConstructor()->isPublic()){
            throw new ContainerException("Constructor of {$definition} must not be public}");
        }

        $resolvedDependencies = [];

        foreach ($dependencies as $dependency) {
            $className = $dependency->getType()->getName();

            if ($dependency->isOptional()) {
                continue;
            }
            if (!$this->isInstantiable($className)) {
                throw new ContainerException("Missing required parameter {$dependency->getName()} of type {$className}");
            }
            $resolvedDependencies[] = $this->make($className);/// в creat funk
        }

        return $resolvedDependencies;
    }

    private function resolveParameters(string $definition): array
    {
        $reflector = new ReflectionClass($definition);

        $parameters = $reflector->hasMethod('__construct') ? $reflector->getConstructor()->getParameters() : [];

        $resolvedParameters = [];

        foreach ($parameters as $methodParameter) {
            $parameterName = $methodParameter->getName();
            $parameterTypeName = $methodParameter->getType()->getName();

            if (array_key_exists($parameterName, $this->with)) {
                $resolvedParameters[] = $this->with[$parameterName];

                continue;
            }
            if ($methodParameter->isOptional()) {
                continue;
            }
            if (!$this->isInstantiable($parameterTypeName)) {
                throw new ContainerException("Missing required parameter {$parameterName} of type {$parameterTypeName}");
            }

            $resolvedParameters[] = $this->make($parameterTypeName);
        }

        return $resolvedParameters;
    }

    public function bind($abstract, $concrete): void
    {
        $this->binds[$abstract] = $concrete;
    }

    private function notInstantiable(string $definition): void
    {
        if (!$this->isInstantiable($definition)) {
            throw new NotFoundException($definition . ' is not instantiable');
        }
    }

    public function isInstantiable(string $definition): bool
    {
        if (array_key_exists($definition, $this->binds)) {
            if (class_exists($this->binds[$definition])) {
                return true;
            }
        }

        if (class_exists($definition)) {
            $reflector = new ReflectionClass($definition);
            if (!$reflector->isAbstract()) {
                return true;
            }
        }

        return false;
    }

    private function markAsInProgress(string $definition): void
    {
        $this->inProgress[$definition] = true;
    }

    private function unmarkAsInProgress(mixed $definition): void
    {
        unset($this->inProgress[$definition]);
    }

////Черновик!!!
    public function has(string $id): bool
    {
        if (array_key_exists($id, $this->resolvedInstances) || $this->isInstantiable($id)) {
            return true;
        }

        return false;
    }


}