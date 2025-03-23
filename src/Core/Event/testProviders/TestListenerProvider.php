<?php

namespace App\Core\Event\testProviders;

use App\Core\Event\AbstractListenerProvider;
use App\Core\Event\testEvent\Event1;
use App\Core\Event\testListener\Listener1;

class TestListenerProvider extends AbstractListenerProvider
{
    public function register(): void
    {
        $this->listeners = [
                Event1::class => [new Listener1(), 'handle']
            ];
    }
}