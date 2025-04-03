<?php

namespace App\Core\Http;

use \GuzzleHttp\Psr7\Response as GuzzleResponse;
use Psr\Http\Message\StreamInterface;

final class HtmlResponse extends GuzzleResponse
{
    public function __construct(string $html, int $status = 200, array $headers = [], string $version = '1.1', ?string $reason = null)
    {
        $headers['Content-Type'] = 'text/html; charset=utf-8';

        parent::__construct($status, $headers, $html, $version, $reason);
    }
}