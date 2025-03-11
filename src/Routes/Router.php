<?php

namespace App\Routes;

class Router
{
    public function runRoute(Route $route)
    {
        $route->run();
    }
}