<?php

namespace App\Blog\Http\Controllers;

use App\Core\Http\JsonResponse;

class NewController
{
    public function index()
    {
        return new JsonResponse(['Hello World!']);
    }
}