<?php

namespace App\Core\Logger\Formatters;

use App\Core\Logger\LogRecord;

class LineFormatter implements FormatterInterface
{
    private string $basePath;

    private bool $allowInlineLineBreaks = true;

    public function format(LogRecord $record): string
    {
        $record = $record->toArray();

        $normalized = $this->normalize($record);

        $json = json_encode($normalized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $this->replaceNewlines($json) . PHP_EOL;
    }

    private function formatException(\Throwable $e): string
    {
        $str = '[object] (' . get_class($e) . '(code: ' . $e->getCode();

        $file = $e->getFile();
        $str .= '): ' . $e->getMessage() . ' at ' . $this->sanitizePath($file) . ':' . $e->getLine();

        // Добавляем стек вызовов
        $str .= $this->getTraceAsString($e);

        return $this->replaceNewlines($str);
    }

    private function normalize(mixed $record): mixed
    {
        if (is_scalar($record)) {
            return $record;
        }

        if (is_array($record)) {
            return array_map([$this, 'normalize'], $record);
        }

        if (is_object($record)) {
            if ($record instanceof \Throwable) {
                return $this->formatException($record);
            }
        }

        return 'unknown:' . var_export($record, true);
    }

    private function replaceNewlines(string $str): string
    {
        if ($this->allowInlineLineBreaks) {
            return str_replace('\\n', "\n", $str);
        }

        return str_replace(["\r\n", "\r", "\n"], ' ', $str);
    }

    private function sanitizePath(string $path): string
    {
        if ($this->basePath) {
            return preg_replace('{^' . preg_quote($this->basePath) . '}', '', $path) ?? $path;
        }

        return $path;
    }

    private function getTraceAsString(\Throwable $e): string
    {
        return "\n" . $e->getTraceAsString();
    }

    public function setBasePath(string $basePath): self
    {
        $this->basePath = $basePath;

        return $this;
    }
}