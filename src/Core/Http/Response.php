<?php

namespace App\Core\Http;

class Response implements ResponseInterface
{
    public function __toString(): string
    {
        dump( 'ddddd');
        return 'ddddd';
    }
}