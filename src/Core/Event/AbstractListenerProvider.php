<?php

namespace App\Core\Event;

use Psr\EventDispatcher\ListenerProviderInterface;

abstract class AbstractListenerProvider implements ListenerProviderInterface
{
    public function __construct()
    {
        $this->register();
    }

    protected array $listeners = [];
    public function getListenersForEvent(object $event): iterable
    {
        $eventName = $event::class;

        if (isset($this->listeners[$eventName])) {
            return $this->listeners[$eventName];
        }

        return [];
    }
    abstract protected function register(): void;
}