<?php

namespace routes;


use App\Blog\Http\Controllers\HomeController;
use App\Blog\Http\Controllers\NewController;
use App\Core\Http\Middleware\testMiddleware\Middleware1;
use App\Core\Http\Middleware\testMiddleware\Middleware2;
use App\Core\Http\Middleware\testMiddleware\Middleware3;
use App\Core\Routing\Router;

return function (Router $router): void {
    $router->group(['prefix' => 'blog','middleware' => Middleware1::class], function (Router $router): void {
        // Это ломает код: Middleware1 в виде строки, а Middleware2 и Middleware3 — в виде массива
        $router->group(['prefix' => 'main','middleware' => [Middleware3::class, Middleware2::class, Middleware1::class]], function (Router $router): void {
            $router->get('/', [HomeController::class, 'index'])->addMiddleware(Middleware3::class);
            $router->get('/{id}', [HomeController::class, 'show']);
            $router->post('/{id}', [HomeController::class, 'store']);
        });
    });
    $router->get('', [NewController::class, 'index'])->addMiddleware(Middleware2::class);
};