<?php

namespace App\Core\Logger;

use App\Core\Logger\Handlers\LogHandlerInterface;

class Logger extends AbstractLogger
{
    ///Конечно же можно сделать обработку несколькими хэндлерами, или передать в конструктор компоновщик
    public function __construct(readonly LogHandlerInterface $handler)
    {}
    public function addRecord(LogLevel $level, \Stringable|string $message, array $context = []): void
    {
        $dateTime = new \DateTimeImmutable();
        $record = new LogRecord($dateTime, $level, $message, $context);

        $this->handler->handle($record);
    }
}