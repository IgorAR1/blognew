<?php

namespace App\Core\configs;

return [
    'host' => $_ENV['REDIS_HOST'],
    'port' => $_ENV['REDIS_PORT'],
];