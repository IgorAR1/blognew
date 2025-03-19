<?php

namespace App\Core\Logger\Handlers;

use App\Core\Logger\LogRecord;

class StreamLogHandler implements LogHandlerInterface
{
    public function __construct(private string $path = '')
    {
//        if (empty($this->path)) {
//            ///BasePath
//        }
    }

    public function handle(LogRecord $record): void
    {

        dump($_ENV);
        dump(getcwd());
        dump($this->path);
        dd(\is_resource(fopen($this->path,'w+')));
    }
}