<?php

namespace App\Core\Logger\Handlers;

use App\Core\Logger\Formatters\FormatterInterface;
use App\Core\Logger\Formatters\LineFormatter;
use App\Core\Logger\LogRecord;
//TODO: это прям точно в переделку
final class StreamLogHandler implements LogHandlerInterface
{
    private FormatterInterface $formatter;

    public function __construct(readonly string $path = '')
    {
        $this->formatter = $this->getFormatter();
    }

    private function getFormatter(): FormatterInterface
    {
        return (new LineFormatter())->setBasePath($this->path);
    }

    public function handle(LogRecord $record): void
    {
        $file = fopen($this->path,'a');

        $record = $this->formatter->format($record);

        fwrite($file, $record);

        fclose($file);
    }
}