<?php

namespace App\Core\Exception;


interface ExceptionHandlerInterface
{
    public function handle(\Throwable $e): void;

}