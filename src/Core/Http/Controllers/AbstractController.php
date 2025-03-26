<?php

namespace App\Core\Http\Controllers;

use Latte\Engine;

abstract class AbstractController
{
    public function __construct(protected Engine $renderEngine)
    {}

    protected function render(string $view, array $params = []): void
    {
        $this->renderEngine->render($view, $params);
    }
}