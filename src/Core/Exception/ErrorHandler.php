<?php

namespace App\Core\Exception;


class ErrorHandler implements ExceptionHandlerInterface
{
    public function __construct()
    {
    }

    public function handle(\Throwable $e): void
    {
        throw $e;
//        $this->logger->alert($e->getMessage(), ['exception' => $e]);
//        return new Response();
        ///generate response / и тд и тп
    }

    private function renderException(): void
    {

    }
}