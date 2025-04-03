<?php

namespace App\Core\Http;

interface SendingResponseInterface
{
    public function send(): void;
}