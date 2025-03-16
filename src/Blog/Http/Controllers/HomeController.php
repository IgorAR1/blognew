<?php

namespace App\Blog\Http\Controllers;

use App\Core\Http\Controllers\ControllerInterface;
use App\Core\Http\RequestInterface;

class HomeController implements ControllerInterface
{
    public function index()
    {
        dd('index');
        return json_encode(['massage' => 'hello']);
    }

    public function show(int $id, RequestInterface $request)
    {
        return json_encode(['massage' => 'hello']);
        dump('show', $id, $request);
    }
}