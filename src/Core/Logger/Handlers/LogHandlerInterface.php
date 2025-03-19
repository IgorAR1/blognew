<?php

namespace App\Core\Logger\Handlers;

use App\Core\Logger\LogRecord;

interface LogHandlerInterface
{
    public function handle(LogRecord $record): void;
}