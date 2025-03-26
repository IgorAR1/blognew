<?php

namespace App\Blog\Http\Controllers;

use App\Core\Http\Controllers\AbstractController;
use App\Core\Http\Controllers\ControllerInterface;
use App\Core\Http\RequestInterface;
use App\Core\Http\Response;
use App\Core\Http\ResponseInterface;

class HomeController extends AbstractController
{
    public function index(): Response
    {
        dd('index');
        return json_encode(['massage' => 'hello']);
    }

    public function show(int $id, RequestInterface $request): ResponseInterface
    {
        $this->render('hello.latte', ['name' => $request->getQueryParams()['name']]);

        return new Response();
    }

    public function store(RequestInterface $request)
    {
        dd($request->getBody());
        $this->render('hello.latte', ['name' => $request->getQueryParams()['name']]);
        return new Response();
    }
}