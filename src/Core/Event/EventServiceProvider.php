<?php

namespace App\Core\Event;

use App\Core\Support\ServiceProvider\ServiceProvider;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class EventServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->application->bind(ListenerProviderInterface::class, fn() => $this->application->makeWith(ListenerProviderComposite::class, ['providers' => $this->application->config('events')]));
        $this->application->bind(EventDispatcherInterface::class, fn() => $this->application->make(EventDispatcher::class));
    }
}