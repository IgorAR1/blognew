<?php

namespace App\Core\Container;

use App\Core\Container\Exceptions\ContainerException;
use App\Core\Container\Exceptions\NotFoundContainerException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;

class Container implements ContainerInterface
{
    /**
     * @var array<object>
     */
    private array $resolvedInstances = [];
    private array $binds = [];
    private array $inProgress = [];

    public function has(string $id): bool
    {
        if (array_key_exists($id, $this->resolvedInstances) || $this->isInstantiable($id)) {
            return true;
        }

        return false;
    }

    public function get(string $id): object
    {
        if (array_key_exists($id, $this->resolvedInstances)) {
            return $this->resolvedInstances[$id];
        }
        $instance = $this->make($id);
        $this->resolvedInstances[$id] = $instance;

        return $instance;
    }

    public function makeWith(string $definition, array $parameters): object
    {
        return $this->make($definition, $parameters);
    }

    public function make(string $definition, array $parameters = []): object
    {

        if (isset($this->inProgress[$definition])) {
            throw new ContainerException('Cyclic dependency resolved instance is already in container');
        }

        if (array_key_exists($definition, $this->binds)) {
            //TODO: тут бы подумать еще мб по лучше есть вариант сделать
            if (is_object($this->binds[$definition])){
                return $this->binds[$definition];
            }
            $definition = $this->binds[$definition];
        }


        $this->notInstantiable($definition);

        $this->markAsInProgress($definition);
        try {
            $resolvedDependencies = $this->resolveDependencies($definition, $parameters);

            $instance = new $definition(...$resolvedDependencies);

            $this->unmarkAsInProgress($definition);
        } catch (\Throwable $exception) {
            $this->unmarkAsInProgress($definition);
            throw $exception;
        }

        return $instance;
    }

    private function resolveDependencies(string $definition, $parameters = []): array
    {
        $reflector = new ReflectionClass($definition);
//        try {
//            $constructor = new \ReflectionMethod($definition, '__construct');
//        } catch (\ReflectionException $e) {
//            return [];
//        }

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
     * @param array<ReflectionParameter> $constructorParameters
     * @param string $definition
     * @return array
     * @throws ContainerException
     */
    private function resolveParameters(array $constructorParameters, string $definition, array $parameters = []): array
    {
        $resolvedParameters = [];

        foreach ($constructorParameters as $methodParameter) {
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
                throw new \ArgumentCountError("Missing required parameter {$parameterName} in {$definition}");
            }

            $parameterTypeName = $methodParameter->getType()->getName();
            if (!$this->isInstantiable($parameterTypeName)) {
                throw new \ArgumentCountError("Missing required parameter {$parameterName} of type {$parameterTypeName} in {$definition}");
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
            throw new NotFoundContainerException($definition . ' is not instantiable');
        }
    }

/////Фантастическое говнище - переделать
    public function isInstantiable(string $definition): bool
    {
        if (array_key_exists($definition, $this->binds)) {
            if (is_object($this->binds[$definition]) || class_exists($this->binds[$definition])) {
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
}