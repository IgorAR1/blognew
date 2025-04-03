<?php

use Dotenv\Dotenv;

include __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$app = new \App\Core\Application\Application();
$request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();

$app->handleRequest($request);


