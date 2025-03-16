<?php

namespace App\Core\Routes;

interface ControllerDispatcherInterface
{
    public function dispatch(array|callable $controller, array $parameters);//ResponseInterface
}