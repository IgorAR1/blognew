<?php

namespace App\Core\Http\Exception;

use App\Core\Http\ResponseInterface;

interface ExceptionHandlerInterface
{
    public function handle(\Throwable $e): ResponseInterface;

}