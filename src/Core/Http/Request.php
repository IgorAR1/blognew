<?php

namespace App\Core\Http;

class Request implements RequestInterface
{
    private string $controller;
    private mixed $attribute;

    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function getUri(): string
    {
        return $_SERVER['REQUEST_URI'];
    }

    public function getParam($key): ?string
    {
        return $_GET[$key] ?? $_POST[$key] ?? null;
    }

    public function getHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headers[str_replace('HTTP_', '', $key)] = $value;
            }
        }

        return $headers;
    }

    public function getBody()
    {
        return file_get_contents('php://input');
    }

    public function getAttribute(string $name,mixed $default = null): mixed
    {
        if (isset($this->attribute[$name])) {
            return $this->attribute[$name];
        }

        return $default;
    }

    public function withAttribute(mixed $attribute): static
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getQueryParams()
    {
        return $_GET;
    }
}