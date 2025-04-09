<?php

namespace App\Core\Container\Exceptions;

use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class NotFoundContainerException extends \Exception implements NotFoundExceptionInterface
{
}