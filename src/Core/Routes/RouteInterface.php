<?php

namespace App\Core\Routes;


use Psr\Http\Message\ServerRequestInterface;

interface RouteInterface
{
    public function getMethod(): string;

    public function getUri(): string;

    public function getCompiled(): string;

    public function getController(): mixed;

    public function getMiddleware(): array;

    public function setController(string $controller): static;
    public function setMiddleware(string $middleware): static;

    public function getParameters(): array;

    public function matches(ServerRequestInterface $request): bool;

    public function bindParameters(ServerRequestInterface $request): void;
}