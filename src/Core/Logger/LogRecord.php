<?php

namespace App\Core\Logger;

use ArrayAccess;

class LogRecord
{
    public function __construct(readonly \DateTimeImmutable $dateTime, readonly LogLevel $level,readonly \Stringable|string $message, readonly array $context = [])
    {}

    public function toArray(): array
    {
        return [
            'datetime' => $this->dateTime->format('Y-m-d H:i:s'),
            'level'=> $this->level->value,
            'message' => $this->message,
            'context' => $this->context,
        ];
    }
}