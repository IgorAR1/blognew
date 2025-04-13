<?php

namespace App\Core\Support\ServiceProvider;

use App\Core\Application\ApplicationInterface;

abstract class ServiceProvider implements ServiceProviderInterface
{
    public function __construct(protected ApplicationInterface $application)
    {
    }
}