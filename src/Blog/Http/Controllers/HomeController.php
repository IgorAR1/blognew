<?php

namespace App\Blog\Http\Controllers;

use App\Core\Http\Controllers\AbstractController;

use App\Core\Http\JsonResponse;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('hello.latte', ['name' => 'vasya']);
    }

    public function show(int $id, ServerRequestInterface $request): ResponseInterface
    {
        return $this->render('hello.latte', ['name' => $request->getQueryParams()['name']]);
    }

    public function store(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse(['message' => 'Hello World!']);
    }
}