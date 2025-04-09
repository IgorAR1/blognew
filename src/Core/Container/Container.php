<?php

namespace App\Core\Container;

use App\Core\Container\Exceptions\ContainerException;
use App\Core\Container\Exceptions\ArgumentCountError;
use App\Core\Container\Exceptions\NotFoundContainerException;
use App\Core\Container\Resolvers\ParametersResolverInterface;
use ReflectionClass;
use ReflectionParameter;

class Container implements ParametersResolverInterface
{
    /**
     * @var array<object>
     */
    private array $resolvedInstances = [];
    private array $binds = [];
    private array $inProgress = [];

    public function has(string $id): bool
    {
        if (isset($this->resolvedInstances[$id]) || $this->isInstantiable($id)) {
            return true;
        }

        return false;
    }

    public function get(string $id): object
    {
        if (isset($this->resolvedInstances[$id])) {
            return $this->resolvedInstances[$id];
        }
        $instance = $this->make($id);
        $this->resolvedInstances[$id] = $instance;

        return $instance;
    }

    public function makeWith(string $abstract, array $parameters): object
    {
        return $this->make($abstract, $parameters);
    }

    public function make(string $abstract, array $parameters = []): object
    {
        if (isset($this->inProgress[$abstract])) {
            throw new ContainerException('Cyclic dependency resolved instance is already in container');
        }

//        $this->resolveAbstract($abstract);???

        if (isset($this->binds[$abstract])) {
            $concrete = $this->binds[$abstract];

            if (is_callable($concrete)) {
                $concrete = $concrete();
                if (!is_object($concrete)) {
                    throw new ContainerException("Factory must return an object");
                }
            }
            //TODO: если будет поддержка alies то тут кончено по другому *никакой поддержки алиасов
            if (is_object($concrete)) {
                if (!$concrete instanceof $abstract) {//Мб в отдельную функцию
                    throw new ContainerException('Concrete must be instance of ' . $abstract);
                }

                return $concrete;
            }
        } else {
            $concrete = $abstract;
        }

        $this->throwNotInstantiable($concrete);

        $this->markAsInProgress($concrete);
        try {
            $resolvedDependencies = $this->resolveDependencies($concrete, $parameters);

            $instance = new $concrete(...$resolvedDependencies);

            if (!$instance instanceof $abstract) {
                throw new ContainerException('Concrete must be instance of ' . $abstract);
            }

            $this->unmarkAsInProgress($concrete);

            return $instance;
        } catch (\Throwable $exception) {
            $this->unmarkAsInProgress($concrete);

            throw $exception;
        }
    }

    protected function resolveDependencies(string $definition, $parameters = []): array
    {
        $reflector = new ReflectionClass($definition);

        $dependencies = [];

        $constructor = $reflector->getConstructor();
        if ($constructor) {
            if (!$constructor->isPublic()) {
                throw new ContainerException("Constructor of {$definition} must be a public}");
            }

            $dependencies = $constructor->getParameters();
        }

        return $this->resolveParameters($dependencies, $definition, $parameters);
    }

    /**
     * @param array<ReflectionParameter> $methodParameters
     */
    public function resolveParameters(array $methodParameters, string $definition, array $parameters = []): array//TODO: Сделать публичным??
    {
        $resolvedParameters = [];

        foreach ($methodParameters as $methodParameter) {
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
                throw new ArgumentCountError("Missing required parameter {$parameterName} in {$definition}");
            }

            $parameterTypeName = $methodParameter->getType()->getName();

            if ($this->isPrimitive($parameterTypeName)) {//Нужно точно знать примитив тут или нет, чтобы корректную ошибку выкинуть
                throw new ArgumentCountError("Missing required parameter {$parameterName} of type {$parameterTypeName} in {$definition}");
            }

            $resolvedParameters[] = $this->make($parameterTypeName);
        }

        return $resolvedParameters;
    }

    public function bind(string $abstract, callable|string|object $concrete): void
    {
        $this->binds[$abstract] = $concrete;
    }

    private function throwNotInstantiable(mixed $concrete): void
    {
        if (!is_string($concrete)) {
            throw new ContainerException("Concrete type of ". gettype($concrete) ." can not be instantiated");
        } elseif (!$this->isInstantiable($concrete)) {
            throw new NotFoundContainerException("{$concrete} is not instantiable");
        }
    }

    private function isInstantiable(string $definition): bool
    {
        if (class_exists($definition)) {
            $reflector = new ReflectionClass($definition);
            return $reflector->isInstantiable();
        }

        return false;
    }

    private function isPrimitive(string $definition): bool
    {
        if (!class_exists($definition) && !interface_exists($definition)) {
            return true;
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
}