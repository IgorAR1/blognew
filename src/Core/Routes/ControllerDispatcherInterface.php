<?php

namespace App\Core\Routes;

interface ControllerDispatcherInterface
{

    public function dispatch(string $controllerDefinition, string $action, array $parameters);//ResponseInterface
}