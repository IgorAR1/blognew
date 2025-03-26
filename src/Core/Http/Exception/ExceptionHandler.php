<?php

namespace App\Core\Http\Exception;

use App\Core\Http\Response;
use App\Core\Http\ResponseInterface;
use Psr\Log\LoggerInterface;

class ExceptionHandler implements ExceptionHandlerInterface
{
//    public function __construct(readonly LoggerInterface $logger)
//    {}

    public function handle(\Throwable $e): ResponseInterface
    {

        throw $e;
//        $this->logger->alert($e->getMessage(), ['exception' => $e]);
        return new Response();
        ///generate response
    }
}