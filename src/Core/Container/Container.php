<?php

namespace App\Core\Container;

use App\Core\Container\Exceptions\ContainerException;
use App\Core\Container\Exceptions\NotFoundContainerException;
use App\Core\Container\Exceptions\ArgumentCountError;
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

    public function makeWith(string $abstract, array $parameters): object
    {
        return $this->make($abstract, $parameters);
    }

    //TODO: definition mb abstract?
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
            }

            //TODO: если будет поддержка alies то тут кончено по другому
            if (is_object($concrete)) {
//                if (is_string($abstract)) {
//                    return $concrete;
//                }
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

    private function resolveDependencies(string $definition, $parameters = []): array
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
                throw new ArgumentCountError("Missing required parameter {$parameterName} in {$definition}");
            }

            $parameterTypeName = $methodParameter->getType()->getName();

//            if (!$this->isInstantiable($parameterTypeName)) {
//                throw new ArgumentCountError("Missing required parameter {$parameterName} of type {$parameterTypeName} in {$definition}");
//            }
            if ($this->isPrimitive($parameterTypeName)) {//Нужно точно знать примитив тут или нет, чтобы корректную ошибку выкинуть
                throw new ArgumentCountError("Missing required parameter {$parameterName} of type {$parameterTypeName} in {$definition}");
            }

            $resolvedParameters[] = $this->make($parameterTypeName);
        }

        return $resolvedParameters;
    }

    public function bind($abstract, $concrete): void
    {
        $this->binds[$abstract] = $concrete;
    }

    private function throwNotInstantiable(string $definition): void
    {
        if (!$this->isInstantiable($definition)) {
            throw new NotFoundContainerException($definition . ' is not instantiable');
        }
    }

/////Фантастическое говнище - переделать
/// ///вообще можно рекурсивно пройти по всему графу
    public function isInstantiable(string $definition): bool
    {
        if (isset($this->binds[$definition])) {
            $definition = $this->binds[$definition];

            if (is_object($definition)) {///callable это прям беда
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

    public function isPrimitive(string $definition): bool
    {
        if (!class_exists($definition) && !interface_exists($definition)) {
            return false;
        }

        return true;
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