<?php

namespace App\Core\Event\testListener;

class Listener1
{
    public function handle()
    {
        dump('event listener');
    }
}