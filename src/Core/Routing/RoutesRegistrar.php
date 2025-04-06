<?php

namespace App\Core\Routing;

class RoutesRegistrar
{
    public function __construct(private Router $router)
    {
    }

    public function registerRoutes(): void
    {
        $this->router->group([],$this->getWebRoutes());

        $this->router->group(['prefix' => 'api'], $this->getApiRoutes());
    }

    private function getApiRoutes(): \Closure
    {
        return require '/var/www/blog/routes/api.php';
    }

    private function getWebRoutes(): \Closure
    {
        return require '/var/www/blog/routes/web.php';
    }

    ///Middleware for api and web
}