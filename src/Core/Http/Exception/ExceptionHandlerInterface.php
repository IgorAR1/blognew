<?php

namespace App\Core\Http\Exception;


interface ExceptionHandlerInterface
{
    public function handle(\Throwable $e): void;

}