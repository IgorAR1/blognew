<?php

namespace App;

class ExampleClass1 implements ExampleInterface
{
    public function __construct(public $name, public ExampleClass2 $exampleClass2,public ExampleClass3 $exampleClass3,public int $number = 2, public string $message = "",...$args)
    {
    }
}