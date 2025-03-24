<?php

namespace App\Core\Event;

use Psr\EventDispatcher\ListenerProviderInterface;

class ListenerProviderComposite implements ListenerProviderInterface
{
    public function __construct(private array $providers)
    {}

    public function getListenersForEvent(object $event): iterable
    {
        $listenersForEvent = [];

        foreach ($this->providers as $provider) {
            $provider = new $provider();
            $listeners = $provider->getListenersForEvent($event);

            if (!is_callable($listeners)) {
                foreach ($listeners as $listener) {
                    $listenersForEvent[] = $listener;
                }
            }else{
                $listenersForEvent[] = $listeners;
            }

        }

        return $listenersForEvent;
    }

    public function addProvider(string $provider): void
    {
        $this->providers[] = $provider;
    }

    public function setProviders(array $providers): void
    {
        $this->providers = $providers;
    }
}