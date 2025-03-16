<?php

namespace App\Core\Routes;

use App\Core\Http\RequestInterface;

interface RouteInterface
{
//    public function run();//ResponseInterface

    public function getCompiled();

    public function getController(): mixed;

    public function getMiddleware(): array;

    public function getAction(): string;

    public function getParameters(): array;

    public function matches(RequestInterface $request);

    public function bindParameters(RequestInterface $request): void;
}