<?php

namespace App\Core\Logger;

use App\Core\Logger\Handlers\LogHandlerInterface;

class Logger extends AbstractLogger
{
    //TODO: решить чет с этим
    ///Конечно же можно сделать обработку несколькими хэндлерами
    /// Если пачку логгеров не нужно обрабатывать то этот класс не нужен - тупо DBLogger FileLogger и тд в коструктор движки и конекшены только передавать
    public function __construct(readonly LogHandlerInterface $handler)
    {}
    public function addRecord(LogLevel $level, \Stringable|string $message, array $context = []): void
    {
        $dateTime = new \DateTimeImmutable();
        $record = new LogRecord($dateTime, $level, $message, $context);

        $this->handler->handle($record);
    }
}