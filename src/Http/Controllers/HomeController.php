<?php

namespace App\Http\Controllers;

class HomeController implements ControllerInterface
{
    public function index()
    {
        dd('index');
        return json_encode(['massage' => 'hello']);
    }

    public function show(int $id)
    {
        dd('show');
        return json_encode(['id' => $id]);
    }
}