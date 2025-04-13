<?php

namespace App\Core\Logger;

use App\Core\Event\ListenerProviderComposite;
use App\Core\Logger\Handlers\LogHandlerInterface;
use App\Core\Logger\Handlers\StreamLogHandler;
use App\Core\Support\ServiceProvider\ServiceProvider;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\LoggerInterface;

class LoggerServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->application->bind(LogHandlerInterface::class, fn() => $this->application->makeWith(StreamLogHandler::class, ['path' => $this->application->config('logs.default.path')]));
        $this->application->bind(LoggerInterface::class, Logger::class);
    }
}