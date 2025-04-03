<?php

namespace App\Core\Http\Controllers;

use App\Core\Http\HtmlResponse;
use Latte\Engine;

abstract class AbstractController
{
    public function __construct(protected Engine $renderEngine)
    {}

    protected function render(string $view, array $params = []): HtmlResponse
    {
        $html = $this->renderEngine->renderToString($view, $params);

        return new HtmlResponse($html);
    }
}