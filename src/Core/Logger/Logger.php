<?php

namespace App\Core\Logger;

use App\Core\Logger\Handlers\LogHandlerInterface;
use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface
{
    public function __construct(readonly LogHandlerInterface $handler)
    {}

    private function addLog(LogLevel $level, \Stringable|string $message, array $context = []): void
    {
        $record = new LogRecord($level, $message, $context);

        $this->handler->handle($record);
    }
    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->addLog(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->addLog(LogLevel::ALERT, $message, $context);
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->addLog(LogLevel::CRITICAL, $message, $context);
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->addLog(LogLevel::ERROR, $message, $context);
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->addLog(LogLevel::WARNING, $message, $context);
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->addLog(LogLevel::NOTICE, $message, $context);
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->addLog(LogLevel::INFO, $message, $context);
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->addLog(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->addLog($level, $message, $context);
    }
}