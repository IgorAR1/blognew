<?php

use App\Container\Container;
use App\ExampleInterface;
use App\ExampleInterface2;

include __DIR__ . '/../vendor/autoload.php';
//
//$reflector = new \ReflectionClass(\App\ExampleClass1::class);
//$parameters = ['dd'];

//foreach ($reflector->getConstructor()->getParameters() as $parameter) {
//    $parameters[] = (string)$parameter->getType();
//}
//$bool = (class_exists($parameters[0]) || interface_exists($parameters[0])); ;
$container = new Container();

$container->bind(\App\ExampleInterface::class, \App\ExampleClass1::class);

$result = $container->makeWith(\App\ExampleInterface::class,['name' => 'Вася','number' => 10]);
//$result = $container->make(ExampleInterface::class);

dd($result);

