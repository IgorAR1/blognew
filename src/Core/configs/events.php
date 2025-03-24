<?php

namespace App\Core\configs;

use App\Core\Event\testEvent\Event1;
use App\Core\Event\testListener\Listener1;
use App\Core\Event\testProviders\TestListenerProvider;
use App\Core\Event\testProviders\TestListenerProvider2;

//Register providers here

return [
    TestListenerProvider::class,
    TestListenerProvider2::class
];