<?php

namespace App\Core\Http\Exception;

use Psr\Log\LoggerInterface;

class ExceptionHandler
{
    public function __construct(readonly LoggerInterface $logger)
    {}

    public function handle(\Exception $e): never
    {
        $this->logger->alert($e->getMessage(), ['exception' => $e]);

        throw $e;
    }
}