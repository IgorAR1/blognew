<?php

namespace App\Core\Event;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(readonly ListenerProviderInterface $listenerProvider)
    {}

    public function dispatch(object $event): object
    {
        $listeners = $this->listenerProvider->getListenersForEvent($event);

        foreach ($listeners as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }
            if (!is_callable($listener)){
                throw new \RuntimeException('Event listener is not a callable');
            }
            $listener($event);
        }

        return $event;
    }
}