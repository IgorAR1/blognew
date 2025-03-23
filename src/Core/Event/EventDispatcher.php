<?php

namespace App\Core\Event;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class EventDispatcher implements EventDispatcherInterface
{
//    private iterable $listeners;

    public function __construct(readonly ListenerProviderInterface $listenerProvider)
    {}

    public function dispatch(object $event): object
    {
        $listeners = $this->listenerProvider->getListenersForEvent($event);

//        if (is_callable($listeners)) {
//            $listeners($event);
//        }

        if (is_iterable($listeners)) {
            foreach ($listeners as $listener) {
                $listener($event);
            }
        }

        return $event;
    }

//    public function addListener(string $eventName, callable $listener): void///А нужно ли ?:/
//    {
//        $this->listeners[$eventName][] = $listener;
//    }
}