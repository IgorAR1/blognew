<?php

namespace App\Core\Http;

interface RequestInterface
{
    public function getMethod(): string;

    public function getUri(): string;

    public function getParam($key): ?string;

    public function getHeaders(): array;

    public function getBody();

    public function getAttribute(string $name,mixed $default = null): mixed;

    public function withAttribute(mixed $attribute): static;

    public function getQueryParams();
}