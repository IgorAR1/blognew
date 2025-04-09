<?php

namespace App\Core\Container\Resolvers;

use Psr\Container\ContainerInterface;

interface ParametersResolverInterface extends ContainerInterface
{
    public function resolveParameters(array $methodParameters, string $definition, array $parameters = []): array;
}