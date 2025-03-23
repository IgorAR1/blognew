<?php

namespace App\Core\Event\testListener;

class Listener2
{
    public function handle()
    {
        dump('event listener2');
    }
}