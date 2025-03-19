<?php

namespace App\Core\Logger;

class LogRecord
{
    public function __construct(LogLevel $level, \Stringable|string $message, array $context = [])
    {}
}