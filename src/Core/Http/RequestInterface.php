<?php

namespace App\Core\Http;

interface RequestInterface
{
    public function getMethod(): string;

    public function getUri(): string;

    public function getParam($key): ?string;

    public function getHeaders(): array;

    public function getBody();

}