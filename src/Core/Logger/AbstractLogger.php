<?php

namespace App\Core\Logger;

use App\Core\Logger\Handlers\LogHandlerInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractLogger implements LoggerInterface
{
    abstract function addRecord(LogLevel $level, \Stringable|string $message, array $context = []): void;

    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->addRecord(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->addRecord(LogLevel::ALERT, $message, $context);
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->addRecord(LogLevel::CRITICAL, $message, $context);
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->addRecord(LogLevel::ERROR, $message, $context);
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->addRecord(LogLevel::WARNING, $message, $context);
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->addRecord(LogLevel::NOTICE, $message, $context);
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->addRecord(LogLevel::INFO, $message, $context);
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->addRecord(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->addRecord($level, $message, $context);
    }
}